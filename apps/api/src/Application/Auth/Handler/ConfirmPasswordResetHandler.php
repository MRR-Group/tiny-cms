<?php

declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\ConfirmPasswordResetCommand;
use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Domain\Auth\Exception\PasswordResetTokenExpiredException;
use App\Domain\Auth\Exception\PasswordResetTokenInvalidException;
use App\Domain\Auth\Repository\UserRepositoryInterface;

class ConfirmPasswordResetHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
    ) {}

    public function handle(ConfirmPasswordResetCommand $command): void
    {
        $user = $this->userRepository->findByResetToken($command->token);

        if ($user === null) {
            throw new PasswordResetTokenInvalidException();
        }

        if (!$user->isResetTokenValid($command->token, new \DateTimeImmutable())) {
            throw new PasswordResetTokenExpiredException();
        }

        $passwordHash = $this->passwordHasher->hash($command->password);
        $user->changePassword($passwordHash);

        $this->userRepository->save($user);
    }
}
