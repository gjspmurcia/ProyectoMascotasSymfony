<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Repository\MascotaRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ApiQRController extends AbstractController{
    
    
    #[Route('/generar/qr/{id}', name: 'api_qr_generar', methods: ['POST'])]
    public function generarQr(Mascota $mascota, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($mascota->getIdUsuario() !== $this->getUser()) {
            return $this->json([
                'status' => 'error',
                'mensaje' => 'No puedes generar un QR para una mascota que no es tuya.',
            ], 403);
        }

        
        $qr = $this->generarQrParaMascota($mascota, $entityManager);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'mensaje' => 'Código QR generado correctamente',
            'qr' => $qr,
        ], 201, [], ['groups' => 'qr:read']);
    }

    //MUESTRA EL PERFIL PUBLICO DE LA MASCOTA
    #[Route('/mostrar/qr/{id}', name: 'api_qr_mostrar', methods: ['GET'])]
    public function mostrarQr(int $id, MascotaRepository $mascotaRepo, MailerInterface $mailer): JsonResponse
    {
        $mascota = $mascotaRepo->find($id);

        if (!$mascota) {
            return $this->json(['mensaje' => 'Mascota no encontrada'], 404);
        }

        $usuario = $mascota->getIdUsuario();

        // Envía el email
        $nombreUsuario = $usuario->getNombre();
        $nombreMascota = $mascota->getNombre();
        $emailMessage = (new Email())
            ->from('infoproyectomascotas@gmail.com')
            ->to($usuario->getEmail())
            ->subject('Tu mascota ha sido escaneada')
            ->html(
                "<p>Hola <strong>{$nombreUsuario}</strong>,</p>".
                "<p>¿Tu mascota se ha perdido? Acabamos de detectar que alguien ha escaneado el código QR de <strong>{$nombreMascota}</strong>.</p>".
                "<p>Si no fuiste tú, es posible que te contacte alguien.</p>"
            );
        $mailer->send($emailMessage);

        return $this->json([
            'nombre_mascota' => $mascota->getNombre(),
            'num_chip' => $mascota->getNumChip(),
            'observaciones' => $mascota->getObservaciones(),
            'foto' => $mascota->getFoto(),
            'nombre_usuario' => $usuario->getNombre(),
            'telefono_usuario' => $usuario->getTelefono(),
            'email_usuario' => $usuario->getEmail(),
        ]);
    }



}
