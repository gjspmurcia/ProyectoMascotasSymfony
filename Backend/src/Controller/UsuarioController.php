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
use Symfony\Component\Routing\Attribute\Route;


final class UsuarioController extends AbstractController{
    
    #[Route('/registro', name: 'app_usuario_registro', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usuario = new Usuario();
        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Encriptar contraseña antes de guardar en la BD
            $hashedPassword = $passwordHasher->hashPassword($usuario, $usuario->getPassword());
            $usuario->setPassword($hashedPassword);

            $entityManager->persist($usuario);
            $entityManager->flush();

            return $this->redirectToRoute('app_usuario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuario/registro.html.twig', [
            'usuario' => $usuario,
            'form' => $form,
        ]);
    }

    #[Route('/area_privada', name: 'app_usuario_index', methods: ['GET'])]
    public function index(UsuarioRepository $usuarioRepository): Response
    {
        // pagina una vez realizado el login
        $usuario = $this->getUser();

        return $this->render('usuario/areaPrivada.html.twig', [
            'usuario' => $usuario,
        ]);
        
    }

    #[Route('/mi_perfil', name: 'app_mi_perfil')]
    public function miPerfil(Security $security, UsuarioRepository $usuarioRepository): Response
    {
        $usuario = $security->getUser();
        $id = $usuario->getId();

        $usuarioBD = $usuarioRepository ->find($id);
        
        if ($usuarioBD)
        {
            // Mostrar la información del usuario
            return $this->render('usuario/miPerfil.html.twig', [
                'usuario' => $usuario
            ]);
        }
        
        // Si usuario no localizado enviar a página de login
        return $this->render('usuario/areaPrivada.html.twig');
        
    }


    #[Route('/mi_perfil_editar/{id}', name: 'app_usuario_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Usuario $usuario, EntityManagerInterface $entityManager): Response
    {
        if ($usuario !== $this->getUser()) 
        {
            throw $this->createAccessDeniedException('No puedes editar un perfil que no es tuyo.');
        }

        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_usuario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuario/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form,
        ]);
    }

    #[Route('/mi_perfil_eliminar/{id}', name: 'app_usuario_delete', methods: ['POST'])]
    public function delete(Request $request, Usuario $usuario, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $usuario) {
            throw $this->createAccessDeniedException('No tienes permiso para eliminar esta cuenta.');
        }

        // si se elimina usuario se borrar todas sus mascotas
        if ($this->isCsrfTokenValid('delete' . $usuario->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logout'); 

    }
}
