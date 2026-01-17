<?php

declare(strict_types=1);

namespace App\Infrastructure\Communication;

use App\Domain\Auth\Service\EmailSenderInterface;
use App\Domain\Auth\ValueObject\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;

final readonly class SymfonyMailerEmailSender implements EmailSenderInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private string $frontendUrl = "http://localhost:5173",
        private string $senderEmail = "no-reply@tinycms.com",
    ) {}

    public function sendPasswordResetLink(Email $recipient, string $resetToken): void
    {
        $resetLink = sprintf("%s/password-reset/confirm?token=%s", $this->frontendUrl, $resetToken);

        $email = (new SymfonyEmail())
            ->from($this->senderEmail)
            ->to((string)$recipient)
            ->subject("Password Reset Request")
            ->text(sprintf(
                "You requested a password reset.\n\nClick the link below to verify your email and set a new password:\n%s\n\nIf you did not request this, please ignore this email.",
                $resetLink,
            ))
            ->html(sprintf(
                "<p>You requested a password reset.</p><p><a href='%s'>Click here to verify your email and set a new password</a></p><p>If you did not request this, please ignore this email.</p>",
                $resetLink,
            ));

        $this->mailer->send($email);
    }
}
