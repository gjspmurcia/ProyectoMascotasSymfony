<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use App\Security\LoginAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;



class ApiSecurityController extends AbstractController
{
    
    #[Route(path: '/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UsuarioRepository $usuarioRepo, UserPasswordHasherInterface $hasher): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'status' => 'error',
                'mensaje' => 'Email y contraseña son obligatorios.',
            ], 400);
        }

        $usuario = $usuarioRepo->findOneBy(['email' => $data['email']]);

        if (!$usuario || !$hasher->isPasswordValid($usuario, $data['password'])) {
            return $this->json([
                'status' => 'error',
                'mensaje' => 'Credenciales inválidas',
            ], 401);
        }

        $token = new UsernamePasswordToken($usuario, 'main', $usuario->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $request->getSession()->set('_security_main', serialize($token));

        return $this->json([
            'status' => 'success',
            'mensaje' => 'Inicio de sesión exitoso',
            'usuario' => $usuario,
        ], 200, [], ['groups' => 'usuario:read']);
    }

    #[Route(path: '/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(SessionInterface $session): JsonResponse
    {
        try {
            // Obtener el token de seguridad
            $tokenStorage = $this->container->get('security.token_storage');
            
            // Invalidar la sesión
            $session->invalidate();
            
            // Limpiar el token de autenticación
            $tokenStorage->setToken(null);
            
            // Limpiar cookies específicas si existen
            $response = new JsonResponse([
                'status' => 'success',
                'mensaje' => 'Sesión cerrada correctamente',
            ]);
            
            // Eliminar la cookie de sesión
            $response->headers->clearCookie('PHPSESSID');
            
            return $response;
    
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'mensaje' => 'Error al cerrar sesión: ' . $e->getMessage(),
            ], 500);
        }
    }
    
}