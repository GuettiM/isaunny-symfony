<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Remplace l'ancien config/mail.php (fonctions sendContactEmail / sendWelcomeEmail).
 */
class MailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $mailFrom,
        private readonly string $mailContact,
    ) {
    }

    public function sendContactEmail(string $nom, string $email, string $message): bool
    {
        $mail = (new TemplatedEmail())
            ->from(new Address($this->mailFrom, 'I.sAunny'))
            ->to($this->mailContact)
            ->replyTo(new Address($email, $nom))
            ->subject('Nouveau message de contact - I.sAunny')
            ->htmlTemplate('emails/contact.html.twig')
            ->context([
                'nom' => $nom,
                'email' => $email,
                'message' => $message,
            ]);

        return $this->send($mail);
    }

    public function sendWelcomeEmail(string $email, string $pseudo): bool
    {
        $mail = (new TemplatedEmail())
            ->from(new Address($this->mailFrom, 'I.sAunny'))
            ->to($email)
            ->subject('Bienvenue sur I.sAunny !')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context([
                'pseudo' => $pseudo,
            ]);

        return $this->send($mail);
    }

    private function send(TemplatedEmail $mail): bool
    {
        try {
            $this->mailer->send($mail);
            return true;
        } catch (TransportExceptionInterface) {
            return false;
        }
    }
}
