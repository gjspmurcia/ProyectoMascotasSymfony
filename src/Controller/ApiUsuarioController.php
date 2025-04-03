<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\UsuarioType;
use App\Repository\UsuarioRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


final class ApiUsuarioController extends AbstractController{
    
    
    #[Route('/api/registro', name: 'api_usuario_registro', methods: ['POST'])]
    public function registro(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)){
            return $this->json([
                'status' => 'error',
                'mensaje' => 'No se enviado los datos',
            ], 400);
        }

        $usuario = new Usuario();

        if (!empty($data['nombre'])){ $usuario->setNombre($data['nombre']); }
        if (!empty($data['telefono'])){ $usuario->setTelefono($data['telefono']); }
        if (!empty($data['email'])){ $usuario->setEmail($data['email']);}
        if (!empty($data['dni'])){ $usuario->setDni($data['dni']);}
        if (!empty($data['calle'])){ $usuario->setCalle($data['calle']);}
        if (!empty($data['num_calle'])){ $usuario->setNumCalle($data['num_calle']);}
        if (!empty($data['cod_postal'])){ $usuario->setCodPostal($data['cod_postal']);}
        if (!empty($data['cuidad'])){ $usuario->setCuidad($data['cuidad']);}        
        if (!empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($usuario, $data['password']);
            $usuario->setPassword($hashedPassword);
        }

        $errores = $validator->validate($usuario);
        if (count($errores) > 0) 
        {
            $errorMessages = [];
            foreach ($errores as $error) {
                $errorMessages[] = [
                    'campo' => $error->getPropertyPath(),
                    'error' => $error->getMessage(),
                ];
            }
            
            return $this->json([
                'status' => 'error',
                'mensaje' => $errorMessages,
            ], 400);
        }

        $entityManager->persist($usuario);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'mensaje' => 'Usuario registrado correctamente',
            'usuario' => $usuario
        ], 201, [], ['groups' => 'usuario:read']);
    }

    #[Route('/api/mi_perfil', name: 'api_mi_perfil', methods: ['GET'])]
    public function miPerfil(Security $security, UsuarioRepository $usuarioRepository): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->json([
                'status' => 'error',
                'mensaje' => 'Usuario no autenticado',
            ], 401);
        }

        return $this->json([
            'status' => 'success',
            'usuario' => $usuario
        ], 200, [], ['groups' => 'usuario:read']);
        
    }


    #[Route('/api/mi_perfil_editar/{id}', name: 'api_usuario_edit', methods: ['PUT'])]
    public function edit(Request $request, Usuario $usuario, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): JsonResponse
    {
        if ($usuario !== $this->getUser()) 
        {
            return $this->json([
                'status' => 'error',
                'mensaje' => 'Usuario no autorizado',
            ], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data)){
            return $this->json([
                'status' => 'error',
                'mensaje' => 'No se enviado los datos',
            ], 400);
        }

        if (!empty($data['nombre'])){ $usuario->setNombre($data['nombre']); }
        if (!empty($data['telefono'])){ $usuario->setTelefono($data['telefono']); }
        if (!empty($data['email'])){ $usuario->setEmail($data['email']);}
        if (!empty($data['dni'])){ $usuario->setDni($data['dni']);}
        if (!empty($data['calle'])){ $usuario->setCalle($data['calle']);}
        if (!empty($data['num_calle'])){ $usuario->setNumCalle($data['num_calle']);}
        if (!empty($data['cod_postal'])){ $usuario->setCodPostal($data['cod_postal']);}
        if (!empty($data['cuidad'])){ $usuario->setCuidad($data['cuidad']);}        
        if (!empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($usuario, $data['password']);
            $usuario->setPassword($hashedPassword);
        }

        $errores = $validator->validate($usuario);
        if (count($errores) > 0) 
        {
            $errorMessages = [];
            foreach ($errores as $error) {
                $errorMessages[] = [
                    'campo' => $error->getPropertyPath(),
                    'error' => $error->getMessage(),
                ];
            }
            return $this->json(['errores' => $errorMessages], 400);
        }

        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'mensaje' => 'Usuario registrado correctamente',
            'usuario' => $usuario
        ], 200, [], ['groups' => 'usuario:read']);
    }

    #[Route('/api/mi_perfil_eliminar/{id}', name: 'api_usuario_delete', methods: ['DELETE'])]
    public function delete(Usuario $usuario, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->getUser() !== $usuario) {
            return $this->json([
                'status' => 'error',
                'mensaje' => 'Usuario no autorizado',
            ], 403);
        }

        $entityManager->remove($usuario);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'mensaje' => 'Usuario eliminado correctamente',
        ], 200);
    }

}
