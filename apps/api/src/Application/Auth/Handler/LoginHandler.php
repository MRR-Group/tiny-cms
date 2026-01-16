<?php

declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\LoginCommand;
use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Application\Auth\Contract\TokenIssuerInterface;
use App\Application\Auth\DTO\AuthTokenView;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\ValueObject\Email;

class LoginHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly TokenIssuerInterface $tokenIssuer,
    ) {
    }

    public function handle(LoginCommand $command): AuthTokenView
    {
        // TODO: Exception handling (user not found, invalid password) should throw Domain/App exceptions
        // mapped to 401 via Middleware/ErrorHandler. For now throwing basic exceptions.

        $user = $this->userRepository->findByEmail(new Email($command->email));

        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        if (!$this->passwordHasher->verify($command->password, $user->getPasswordHash())) {
            throw new \Exception('Invalid credentials');
        }

        $token = $this->tokenIssuer->issue($user);

        return new AuthTokenView($token);
    }
}
