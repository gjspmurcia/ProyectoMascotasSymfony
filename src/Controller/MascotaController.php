<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Form\MascotaType;
use App\Repository\MascotaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mascota')]
final class MascotaController extends AbstractController{
    #[Route(name: 'app_mascota_index', methods: ['GET'])]
    public function index(MascotaRepository $mascotaRepository): Response
    {
        return $this->render('mascota/index.html.twig', [
            'mascotas' => $mascotaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_mascota_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mascotum = new Mascota();
        $form = $this->createForm(MascotaType::class, $mascotum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($mascotum);
            $entityManager->flush();

            return $this->redirectToRoute('app_mascota_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mascota/new.html.twig', [
            'mascotum' => $mascotum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mascota_show', methods: ['GET'])]
    public function show(Mascota $mascotum): Response
    {
        return $this->render('mascota/show.html.twig', [
            'mascotum' => $mascotum,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mascota_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Mascota $mascotum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MascotaType::class, $mascotum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_mascota_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mascota/edit.html.twig', [
            'mascotum' => $mascotum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mascota_delete', methods: ['POST'])]
    public function delete(Request $request, Mascota $mascotum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mascotum->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($mascotum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mascota_index', [], Response::HTTP_SEE_OTHER);
    }
}
