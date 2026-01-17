<?php

declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\LoginCommand;
use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Application\Auth\Contract\TokenIssuerInterface;
use App\Application\Auth\DTO\AuthTokenView;
use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\Exception\UserNotFoundException;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\ValueObject\Email;

class LoginHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly TokenIssuerInterface $tokenIssuer,
    ) {}

    public function handle(LoginCommand $command): AuthTokenView
    {
        $user = $this->userRepository->findByEmail(new Email($command->email));

        if (!$user) {
            throw new UserNotFoundException();
        }

        if (!$this->passwordHasher->verify($command->password, $user->getPasswordHash())) {
            throw new InvalidCredentialsException();
        }

        $token = $this->tokenIssuer->issue($user);

        return new AuthTokenView($token);
    }
}
