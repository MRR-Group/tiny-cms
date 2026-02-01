<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth\Handler;

use App\Application\Auth\Command\ConfirmPasswordResetCommand;
use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Application\Auth\Handler\ConfirmPasswordResetHandler;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\PasswordResetTokenExpiredException;
use App\Domain\Auth\Exception\PasswordResetTokenInvalidException;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ConfirmPasswordResetHandlerTest extends TestCase
{
    public function testHandleChangesPasswordIfTokenValid(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $handler = new ConfirmPasswordResetHandler($userRepository, $passwordHasher);

        $command = new ConfirmPasswordResetCommand("valid-token", "new-password");

        $user = $this->createMock(User::class);

        $userRepository->expects($this->once())
            ->method("findByResetToken")
            ->with("valid-token")
            ->willReturn($user);

        $user->expects($this->once())
            ->method("isResetTokenValid")
            ->with("valid-token", $this->isInstanceOf(\DateTimeImmutable::class))
            ->willReturn(true);

        $passwordHasher->expects($this->once())
            ->method("hash")
            ->with("new-password")
            ->willReturn("hashed-password");

        $user->expects($this->once())
            ->method("changePassword")
            ->with("hashed-password");

        $userRepository->expects($this->once())
            ->method("save")
            ->with($user);

        $handler->handle($command);
    }

    public function testHandleThrowsIfTokenInvalidOrUserNotFound(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $handler = new ConfirmPasswordResetHandler($userRepository, $passwordHasher);

        $command = new ConfirmPasswordResetCommand("invalid-token", "new-password");

        $userRepository->expects($this->once())
            ->method("findByResetToken")
            ->with("invalid-token")
            ->willReturn(null);

        $this->expectException(PasswordResetTokenInvalidException::class);

        $handler->handle($command);
    }

    public function testHandleThrowsIfTokenExpired(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $handler = new ConfirmPasswordResetHandler($userRepository, $passwordHasher);

        $command = new ConfirmPasswordResetCommand("expired-token", "new-password");

        $user = $this->createMock(User::class);

        $userRepository->expects($this->once())
            ->method("findByResetToken")
            ->with("expired-token")
            ->willReturn($user);

        $user->expects($this->once())
            ->method("isResetTokenValid")
            ->with("expired-token")
            ->willReturn(false);

        $this->expectException(PasswordResetTokenExpiredException::class);

        $handler->handle($command);
    }
}
