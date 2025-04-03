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
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;


class ApiSecurityController extends AbstractController
{
    
    #[Route(path: '/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UsuarioRepository $usuarioRepo, UserPasswordHasherInterface $hasher, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $loginAuthenticator): JsonResponse 
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

        $userAuthenticator->authenticateUser($usuario,$loginAuthenticator,$request);

        return $this->json([
            'status' => 'success',
            'mensaje' => 'Inicio de sesión exitoso',
            'usuario' => $usuario,
        ], 200, [], ['groups' => 'usuario:read']);
    }

    #[Route(path: '/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(SessionInterface $session): JsonResponse
    {
        $session->invalidate();

        return $this->json([
            'status' => 'success',
            'mensaje' => 'Sesión cerrada correctamente',
        ]);
    }
}
