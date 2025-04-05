<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Entity\QR;
use App\Form\MascotaType;
use App\Repository\MascotaRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;


final class MascotaController extends AbstractController{
    
    #[Route('/listado_mascotas', name: 'app_mascota_index', methods: ['GET'])]
    public function index(MascotaRepository $mascotaRepository): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            throw $this->createAccessDeniedException('Debes iniciar sesión para ver tus mascotas.');
        }

        return $this->render('mascota/index.html.twig', [
            'mascotas' => $mascotaRepository->findBy(['id_usuario' => $this->getUser()]),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/mi_mascota_nueva', name: 'app_mascota_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $mascota = new Mascota();
        $form = $this->createForm(MascotaType::class, $mascota);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $mascota->setIdUsuario($this->getUser());

            $fotoFile = $form->get('foto')->getData();
            if ($fotoFile) {
                $newFilename = $this->subirFotoMascota($fotoFile, $mascota->getNombre(), $this->getUser()->getId(), $slugger);
                $mascota->setFoto($newFilename);
            }

            $entityManager->persist($mascota);
            $entityManager->flush();

            // generar QR automaticamente
            $this->generarQrParaMascota($mascota, $entityManager);
            $entityManager->flush();

            return $this->redirectToRoute('app_mascota_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mascota/new.html.twig', [
            'mascota' => $mascota,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/mi_mascota_editar/{id}', name: 'app_mascota_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Mascota $mascota, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if ($mascota->getIdUsuario() !== $this->getUser()) 
        {
            throw $this->createAccessDeniedException('No puedes editar una mascota que no es tuya.');
        }

        $form = $this->createForm(MascotaType::class, $mascota);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $fotoFile = $form->get('foto')->getData();
            if ($fotoFile) {
                $newFilename = $this->subirFotoMascota($fotoFile, $mascota->getNombre(), $this->getUser()->getId(), $slugger);
                $mascota->setFoto($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_mascota_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mascota/edit.html.twig', [
            'mascota' => $mascota,
            'form' => $form,
        ]);
    }

    #[Route('/mi_mascota_eliminar/{id}', name: 'app_mascota_delete', methods: ['POST'])]
    public function delete(Request $request, Mascota $mascota, EntityManagerInterface $entityManager): Response
    {
        if ($mascota->getIdUsuario() !== $this->getUser()) 
        {
            throw $this->createAccessDeniedException('No puedes eliminar una mascota que no es tuya.');
        }

        if ($this->isCsrfTokenValid('delete'.$mascota->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($mascota);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mascota_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/generar/qr/{id}', name: 'app_qr_generar', methods: ['GET', 'POST'])]
    public function generarQr(Mascota $mascota, MascotaRepository $mascotaRepository, EntityManagerInterface $entityManager): JsonResponse
    {
                
        $qr = $this->generarQrParaMascota($mascota, $entityManager);
        $entityManager->flush();

        return $this->render('mascota/index.html.twig', [
            'mascotas' => $mascotaRepository->findBy(['id_usuario' => $this->getUser()]),
        ]);
    }


    private function subirFotoMascota(UploadedFile $fotoFile, string $nombreMascota, int $idUsuario, SluggerInterface $slugger): string
    {
        $fecha = (new \DateTime())->format('Ymd_His');
        $extension = $fotoFile->guessExtension();
        $safeNombre = $slugger->slug($nombreMascota);
        $newFilename = $safeNombre . '_' . $idUsuario . '_' . $fecha . '.' . $extension;

        $fotoFile->move(
            $this->getParameter('fotos_mascotas_directory'),
            $newFilename
        );

        return $newFilename;
    }

    private function generarQrParaMascota(Mascota $mascota, EntityManagerInterface $entityManager): QR
    {
        $contenidoQR = 'http://localhost:8000//mostrar/qr/' . $mascota->getId();
        $urlQr = 'https://quickchart.io/qr?text=' . urlencode($contenidoQR) . '&size=200';

        $qr = new QR();
        $qr->setIdMascota($mascota);
        $qr->setImgQr($urlQr);

        $entityManager->persist($qr);

        return $qr;
    }



}
