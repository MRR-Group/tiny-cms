<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth;

use App\Application\Auth\Command\LoginCommand;
use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Application\Auth\Contract\TokenIssuerInterface;
use App\Application\Auth\Handler\LoginHandler;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\Exception\UserNotFoundException;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoginHandlerTest extends TestCase
{
    private LoginHandler $handler;
    private UserRepositoryInterface&MockObject $repository;
    private PasswordHasherInterface&MockObject $hasher;
    private TokenIssuerInterface&MockObject $issuer;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $this->hasher = $this->createMock(PasswordHasherInterface::class);
        $this->issuer = $this->createMock(TokenIssuerInterface::class);
        $this->handler = new LoginHandler($this->repository, $this->hasher, $this->issuer);
    }

    public function testSuccessfulLogin(): void
    {
        $email = "test@example.com";
        $password = "secret";
        $hash = "hashed_secret";
        $user = new User(UserId::generate(), new Email($email), Role::admin(), $hash);

        $this->repository->expects($this->once())
            ->method("findByEmail")
            ->willReturn($user);

        $this->hasher->expects($this->once())
            ->method("verify")
            ->with($password, $hash)
            ->willReturn(true);

        $this->issuer->expects($this->once())
            ->method("issue")
            ->with($user)
            ->willReturn("jwt_token");

        $result = $this->handler->handle(new LoginCommand($email, $password));

        $this->assertEquals("jwt_token", $result->token);
    }

    public function testLoginFailsIfUserNotFound(): void
    {
        $this->repository->method("findByEmail")->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("User not found");

        $this->handler->handle(new LoginCommand("unknown@mail.com", "password"));
    }

    public function testLoginFailsIfPasswordInvalid(): void
    {
        $user = new User(UserId::generate(), new Email("test@example.com"), Role::admin(), "hash");
        $this->repository->method("findByEmail")->willReturn($user);

        $this->hasher->method("verify")->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage("Invalid credentials provided");

        $this->handler->handle(new LoginCommand("test@example.com", "wrong"));
    }
}
