<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth;

use App\Application\Auth\Command\RequestPasswordResetCommand;
use App\Application\Auth\Handler\RequestPasswordResetHandler;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\Service\EmailSenderInterface;
use App\Domain\Auth\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class RequestPasswordResetHandlerTest extends TestCase
{
    public function testHandleSendsEmailIfUserExists(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $emailSender = $this->createMock(EmailSenderInterface::class);
        $handler = new RequestPasswordResetHandler($userRepository, $emailSender);

        $command = new RequestPasswordResetCommand("test@example.com");
        $user = $this->createMock(User::class);
        $user->method("getEmail")->willReturn(new Email("test@example.com"));

        $userRepository->expects($this->once())
            ->method("findByEmail")
            ->with($this->callback(fn(Email $email) => (string)$email === "test@example.com"))
            ->willReturn($user);

        $user->expects($this->once())
            ->method("setResetToken")
            ->with(
                $this->callback(function (string $token) {
                    return strlen($token) === 64; // hex string of 32 bytes = 64 chars
                }),
                $this->isInstanceOf(\DateTimeImmutable::class),
            );

        $userRepository->expects($this->once())
            ->method("save")
            ->with($user);

        $emailSender->expects($this->once())
            ->method("sendPasswordResetLink")
            ->with($this->callback(fn(Email $email) => (string)$email === "test@example.com"), $this->isType("string"));

        $handler->handle($command);
    }

    public function testHandleDoesNothingIfUserNotFound(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $emailSender = $this->createMock(EmailSenderInterface::class);
        $handler = new RequestPasswordResetHandler($userRepository, $emailSender);

        $command = new RequestPasswordResetCommand("unknown@example.com");

        $userRepository->expects($this->once())
            ->method("findByEmail")
            ->willReturn(null);

        $userRepository->expects($this->never())->method("save");
        $emailSender->expects($this->never())->method("sendPasswordResetLink");

        $handler->handle($command);
    }
}
