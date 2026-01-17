<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth;

use App\Application\Auth\Command\ChangePasswordCommand;
use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Application\Auth\Handler\ChangePasswordHandler;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\Exception\UserNotFoundException;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChangePasswordHandlerTest extends TestCase
{
    private ChangePasswordHandler $handler;
    private UserRepositoryInterface&MockObject $repository;
    private PasswordHasherInterface&MockObject $hasher;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $this->hasher = $this->createMock(PasswordHasherInterface::class);
        $this->handler = new ChangePasswordHandler($this->repository, $this->hasher);
    }

    public function testSuccessfulPasswordChange(): void
    {
        $userId = UserId::generate();
        $user = $this->createMock(User::class);
        $user->method("getPasswordHash")->willReturn("old_hash");

        $command = new ChangePasswordCommand($userId, "old", "new");

        $this->repository->method("findById")->with($userId)->willReturn($user);

        $this->hasher->expects($this->once())
            ->method("verify")
            ->with("old", "old_hash")
            ->willReturn(true);

        $this->hasher->expects($this->once())
            ->method("hash")
            ->with("new")
            ->willReturn("new_hash");

        $user->expects($this->once())->method("changePassword")->with("new_hash");
        $this->repository->expects($this->once())->method("save")->with($user);

        $this->handler->handle($command);
    }

    public function testFailsIfUserNotFound(): void
    {
        $this->repository->method("findById")->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("User not found");

        $this->handler->handle(new ChangePasswordCommand(UserId::generate(), "old", "new"));
    }

    public function testFailsIfOldPasswordInvalid(): void
    {
        $user = $this->createMock(User::class);
        $user->method("getPasswordHash")->willReturn("old_hash");

        $this->repository->method("findById")->willReturn($user);
        $this->hasher->method("verify")->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage("Invalid credentials provided");

        $this->handler->handle(new ChangePasswordCommand(UserId::generate(), "wrong", "new"));
    }
}
