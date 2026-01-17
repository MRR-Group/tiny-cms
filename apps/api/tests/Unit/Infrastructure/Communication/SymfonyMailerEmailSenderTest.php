<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Communication;

use App\Domain\Auth\ValueObject\Email;
use App\Infrastructure\Communication\SymfonyMailerEmailSender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;

class SymfonyMailerEmailSenderTest extends TestCase
{
    public function testSendPasswordResetLink(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method("send")
            ->with($this->callback(function (SymfonyEmail $email) {
                $this->assertSame("Password Reset Request", $email->getSubject());
                $this->assertSame("no-reply@tinycms.com", $email->getFrom()[0]->getAddress());
                $this->assertSame("recipient@example.com", $email->getTo()[0]->getAddress());

                $textWithLink = "reset-password?token=token123";
                $this->assertStringContainsString($textWithLink, $email->getTextBody());
                $this->assertStringContainsString($textWithLink, $email->getHtmlBody());

                return true;
            }));

        $sender = new SymfonyMailerEmailSender($mailer, "http://localhost", "no-reply@tinycms.com");

        $sender->sendPasswordResetLink(new Email("recipient@example.com"), "token123");
    }
}
