<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth;

use App\Application\Auth\Command\CreateUserCommand;
use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Application\Auth\Handler\CreateUserHandler;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\UserAlreadyExistsException;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateUserHandlerTest extends TestCase
{
    private CreateUserHandler $handler;
    private UserRepositoryInterface&MockObject $repository;
    private PasswordHasherInterface&MockObject $hasher;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $this->hasher = $this->createMock(PasswordHasherInterface::class);
        $this->handler = new CreateUserHandler($this->repository, $this->hasher);
    }

    public function testSuccessfulCreation(): void
    {
        $command = new CreateUserCommand("new@example.com", "password", "editor");

        $this->repository->expects($this->once())
            ->method("findByEmail")
            ->willReturn(null);

        $this->hasher->expects($this->once())
            ->method("hash")
            ->with("password")
            ->willReturn("hashed");

        $this->repository->expects($this->once())
            ->method("save")
            ->with($this->callback(fn(User $user) => $user->getEmail()->toString() === "new@example.com"
                    && $user->getRole()->toString() === "editor"
                    && $user->getPasswordHash() === "hashed"
                    && $user->mustChangePassword() === true));

        $this->handler->handle($command);
    }

    public function testFailsIfUserExists(): void
    {
        $command = new CreateUserCommand("existing@example.com", "password", "editor");

        $this->repository->method("findByEmail")->willReturn($this->createMock(User::class));

        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage("User with email 'existing@example.com' already exists");

        $this->handler->handle($command);
    }
}
