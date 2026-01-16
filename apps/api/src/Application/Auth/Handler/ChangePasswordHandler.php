<?php

declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\ChangePasswordCommand;
use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Domain\Auth\Repository\UserRepositoryInterface;

class ChangePasswordHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function handle(ChangePasswordCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);

        if (!$user) {
            throw new \Exception('User not found');
        }

        if (!$this->passwordHasher->verify($command->oldPassword, $user->getPasswordHash())) {
            throw new \Exception('Invalid old password');
        }

        $newHash = $this->passwordHasher->hash($command->newPassword);
        $user->changePassword($newHash);

        $this->userRepository->save($user);
    }
}
