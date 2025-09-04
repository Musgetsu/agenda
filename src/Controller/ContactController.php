<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ContactController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/contact', name: 'contact', methods: ['POST'])]
    public function contact(Request $request, MailerInterface $mailer): JsonResponse
    {
        $data = $request->request->all();

        $name = htmlspecialchars($data['name'] ?? '');
        $email_address = htmlspecialchars($data['email'] ?? '');
        $message = htmlspecialchars($data['message'] ?? '');
        $recaptcha_response = $data['recaptcha_response'] ?? '';

        // Clé secrète reCAPTCHA v3
        $recaptcha_secret = $_ENV['RECAPTCHA_SECRET'] ?? '';

        // Vérification reCAPTCHA
        try {
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $recaptcha_secret,
                    'response' => $recaptcha_response,
                ],
            ]);

            $result = $response->toArray();

            if (!($result['success'] ?? false) || ($result['score'] ?? 0) < 0.5 || ($result['action'] ?? '') !== 'contact') {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Échec de la vérification reCAPTCHA.'
                ]);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Impossible de vérifier reCAPTCHA.'
            ]);
        }

        // Envoi du mail
        try {
            $email = (new Email())
                ->from(new Address('webmaster@rohan-martin.fr', $name . ' depuis rohan-martin.fr'))
                ->to('contact@rohan-martin.fr')
                ->replyTo(new Address($email_address, $name))
                ->subject('Message depuis rohan-martin.fr')
                ->text(strip_tags($message))
                ->html(nl2br($message));

            $mailer->send($email);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Mail envoyé avec succès !'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Erreur lors de l\'envoi de l\'email.'
            ]);
        }
    }
}
