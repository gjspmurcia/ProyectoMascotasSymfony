<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Form\MascotaType;
use App\Repository\MascotaRepository;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;




final class ApiMascotaController extends AbstractController{
    
    #[Route('/api/mi_mascota', name: 'api_mascota_index', methods: ['GET'])]
    public function index(MascotaRepository $mascotaRepository): JsonResponse
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            
            return $this->json([
                'status' => 'error',
                'mensaje' => 'Debes iniciar sesión para ver tus mascotas.',
            ], 403);
        }

        $mascota = $mascotaRepository->findBy(['id_usuario' => $this->getUser()]);

        return $this->json([
            'status' => 'success',
            'mascota' => $mascota
        ], 200, [], ['groups' => 'mascota:read']);
        
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/api/mi_mascota_nueva', name: 'api_mascota_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)){
            return $this->json([
                'status' => 'error',
                'mensaje' => 'No se enviado los datos',
            ], 403);
        }

        $mascota = new Mascota();
        $mascota->setIdUsuario($this->getUser());

        if (!empty($data['nombre'])) { $mascota->setNombre($data['nombre']);}

        if (!empty($data['num_chip'])) { $mascota->setNumChip($data['num_chip']);}
    
        if (!empty($data['observaciones'])) { $mascota->setObservaciones($data['observaciones']);}

        $fotoFile = $request->files->get('foto');
        if ($fotoFile) 
        {
            $newFilename = $this->subirFotoMascota($fotoFile, $mascota->getNombre(), $this->getUser()->getId(), $slugger);
            $mascota->setFoto($newFilename);
        }    

        $entityManager->persist($mascota);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'mensaje' => 'Mascota creada correctamente',
            'mascota' => $mascota
        ], 201, [], ['groups' => 'mascota:read']);
        
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/api/mi_mascota_editar/{id}', name: 'api_mascota_edit', methods: ['PUT'])]
    public function edit(Request $request, Mascota $mascota, EntityManagerInterface $entityManager, SluggerInterface $slugger): JsonResponse
    {
        if ($mascota->getIdUsuario() !== $this->getUser()) {
            
            return $this->json([
                'status' => 'error',
                'mensaje' => 'Usuario no autorizado',
            ], 403);
        }
    
        $data = json_decode($request->getContent(), true);
    
        if (!empty($data['nombre'])) { $mascota->setNombre($data['nombre']); }
    
        if (!empty($data['num_chip'])) { $mascota->setNumChip($data['num_chip']);}
    
        if (!empty($data['observaciones'])) { $mascota->setObservaciones($data['observaciones']);}
      
        $fotoFile = $request->files->get('foto');
        if ($fotoFile) 
        {
            $newFilename = $this->subirFotoMascota($fotoFile, $mascota->getNombre(), $this->getUser()->getId(), $slugger);
            $mascota->setFoto($newFilename);
        }
        
    
        $entityManager->flush();
    
        return $this->json([
            'status' => 'success',
            'mensaje' => 'Mascota actualizada correctamente',
            'mascota' => $mascota
        ], 201, [], ['groups' => 'mascota:read']);
    }
    
    #[IsGranted('ROLE_USER')]
    #[Route('/api/mi_mascota_eliminar/{id}', name: 'api_mascota_delete', methods: ['DELETE'])]
    public function delete(Request $request, Mascota $mascota, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($mascota->getIdUsuario() !== $this->getUser()) 
        {
            return $this->json([
                'status' => 'error',
                'mensaje' => 'Usuario no autorizado',
            ], 403);
        }

        $entityManager->remove($mascota);
        $entityManager->flush();
        
        return $this->json([
            'status' => 'success',
            'mensaje' => 'Mascota eliminada correctamente',
            'mascota' => $mascota
        ], 200);
    
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



}
