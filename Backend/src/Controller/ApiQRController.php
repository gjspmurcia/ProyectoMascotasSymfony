<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Repository\MascotaRepository;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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

    #[Route('/descargar/qr/{id}', name: 'descargar_qr')]
    public function descargarQr(Mascota $mascota): Response
    {
        $contenidoQR = $this->getParameter('url') . '/mascota/' . $mascota->getId();
        $urlQr = 'https://quickchart.io/qr?text=' . urlencode($contenidoQR) . '&size=350';

        // Crear un archivo temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'QR_' . $mascota->getNombre()) . '.png';

        // Descargar el QR desde la URL
        file_put_contents($tempFile, file_get_contents($urlQr));

        // Verificar que se haya descargado correctamente
        if (!file_exists($tempFile)) {
            throw $this->createNotFoundException('No se pudo descargar el código QR.');
        }

        // Crear la respuesta de descarga
        $response = new BinaryFileResponse($tempFile);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'QR_' . $mascota->getNombre() . '.png'
        );

        // Eliminar el archivo temporal después de enviar la respuesta
        $response->deleteFileAfterSend(true);

        return $response;
    }

    //MUESTRA EL PERFIL PUBLICO DE LA MASCOTA
    #[Route('/mostrar/qr/{id}', name: 'api_qr_mostrar', methods: ['GET'])]
    public function mostrarQr(int $id, MascotaRepository $mascotaRepo, MailerInterface $mailer, LoggerInterface $logger): JsonResponse
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
            ->subject('Aviso: código QR de ' . $nombreMascota . ' escaneado')
            ->html(
                "<p>Hola <strong>{$nombreUsuario}</strong>,</p>".
                "<p>¿Tu mascota se ha perdido? </p>".
                "<p>Acabamos de detectar que alguien ha escaneado el código QR de <strong>{$nombreMascota}</strong>.</p>".
                "<p>Si no fuiste tú, es posible que te contacte alguien. Estate atento.</p>".
                "<p>Si es un error, ignora este mensaje.</p>".
                "<p>Un saludo de Equipo Mi Huella APP.</p>"
        );

        try {
            $mailer->send($emailMessage);
        } catch (TransportExceptionInterface $e) {
            $logger->error('Error enviando email: '.$e->getMessage(), [
            'exception' => $e,
            ]);}

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

    // enviar aviso dueño
    #[Route('/api/enviar-aviso/{id}', name: 'api_enviar_aviso', methods: ['POST'])]
    public function enviarAviso(int $id, Request $request, MascotaRepository $mascotaRepo, MailerInterface $mailer, LoggerInterface $logger): JsonResponse
    {
        $mascota = $mascotaRepo->find($id);

        if (!$mascota) {
            return $this->json(['mensaje' => 'Mascota no encontrada'], 404);
        }

        // Extraer JSON con el mensaje
        $data = json_decode($request->getContent(), true);
        $mensajeUsuario = $data['mensaje'] ?? '';

        $usuario = $mascota->getIdUsuario();

        // Envía el email
        $nombreUsuario = $usuario->getNombre();
        $nombreMascota = $mascota->getNombre();
        $emailMessage = (new Email())
            ->from('infoproyectomascotas@gmail.com')
            ->to($usuario->getEmail())
            ->subject('Mensaje recibido: han escaneado el código QR de ' . $nombreMascota)
            ->html(
                "<p>Hola <strong>{$nombreUsuario}</strong>,</p>".
                "<p>La persona que ha escaneado el código QR de <strong>{$nombreMascota}</strong> te ha escrito un mensaje.</p>".
                "<p>Mensaje de esa persona:</p>".
                "<blockquote>{$mensajeUsuario}</blockquote>".
                "<p>Un saludo de Equipo Mi Huella APP.</p>"
            );
        
        try {
            $mailer->send($emailMessage);
        } catch (TransportExceptionInterface $e) {
            $logger->error('Error enviando email: '.$e->getMessage(), [
            'exception' => $e,
            ]);}

        return $this->json([
            'status' => 'success',
            'mensaje' => 'Email enviado correctamente'
        ], 201, [], []);
    }


}