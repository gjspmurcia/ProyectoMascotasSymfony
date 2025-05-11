<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ApiResetPasswordController extends AbstractController
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer,
        private UserPasswordHasherInterface $passwordHasher,
        private LoggerInterface $logger,
    ) {}

    #[Route('/api/recuperar-password', name: 'api_forgot_password', methods: ['POST'])]
    public function request(Request $request): JsonResponse
    {
        $this->logger->info('Iniciando petición de recuperar-password');

        $content = $request->getContent();
        $this->logger->debug('Contenido raw del request', ['content' => $content]);

        $data = json_decode($request->getContent(), true);
        $email = $data['email'];

        $this->logger->debug('Email extraído', ['email' => $email]);

        if (!$email) {
            return $this->json(['mensaje'=>'Email obligatorio'], 400);
        }

        $usuario = $this->getDoctrine()
                        ->getRepository(Usuario::class)
                        ->findOneBy(['email' => $email]);
        
        $this->logger->debug('Resultado findOneBy email', ['usuario' => $usuario?->getId()]);

        // Por seguridad, no dar info sobre los email existentes
        if (!$usuario) {
            return $this->json(['mensaje'=>'Si existe ese email, recibirás un enlace'], 200);
        }

        // Genera el token
        $resetToken = $this->resetPasswordHelper->generateResetToken($usuario);

        // Construye el enlace que recibirá el usuario en el correo
        $frontendUrl = sprintf(
            'http://localhost:4321/nuevo-password/%s?token=%s',
            $resetToken->getSelector(),
            $resetToken->getToken()
        );
        $this->logger->debug('URL de frontend construida', ['url'=>$frontendUrl]);

        // Envía el email
        $emailMessage = (new Email())
            ->from('infoproyectomascotas@gmail.com') //configurado en .env
            ->to($usuario->getEmail())
            ->subject('Recupera tu contraseña')
            ->text("Para restablecer tu contraseña, visita: $frontendUrl")
            ->html("<p>Para restablecer tu contraseña, haz clic <a href=\"$frontendUrl\">aquí</a>.</p>");
        try{
            $this->mailer->send($emailMessage);
            $this->logger->info('Email enviado correctamente');
        }
        catch (\Throwable $e) {
            
            $this->get('logger')->error('Error enviando mail: '.$e->getMessage());
            return $this->json(['mensaje'=>'Error interno al enviar el correo.'], 500);
        }

        return $this->json(['mensaje'=>'Si existe ese email, recibirás un enlace'], 200);
    }

    #[Route('/api/reset-password', name: 'api_reset_password', methods: ['POST'])]
    public function reset(Request $request): JsonResponse
    {
        $data     = json_decode($request->getContent(), true);
        $selector = $data['selector'];
        $token    = $data['token'];
        $password = $data['password'];

        if (!$selector || !$token || !$password) {
            return $this->json(['mensaje'=>'Datos incompletos'], 400);
        }

        try {
            // Validar y recuperar el usuario
            $usuario = $this->resetPasswordHelper
                             ->validateTokenAndFetchUser($selector, $token);
        } catch (\Exception $e) {
            return $this->json(['mensaje'=>'Enlace inválido o expirado'], 400);
        }

        // Hash y guardar la nueva contraseña
        $hashed = $this->passwordHasher->hashPassword($usuario, $password);
        $usuario->setPassword($hashed);

        $em = $this->getDoctrine()->getManager();
        $em->persist($usuario);
        $em->flush();

        // Invalidar el token (eliminarlo)
        $this->resetPasswordHelper->removeResetRequest($selector);

        return $this->json(['mensaje'=>'Contraseña actualizada'], 200);
    }
}
