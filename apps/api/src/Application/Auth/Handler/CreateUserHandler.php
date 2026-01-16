<?php

declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\CreateUserCommand;
use App\Application\Auth\Contract\PasswordHasherInterface;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;

final class CreateUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function handle(CreateUserCommand $command): void
    {
        $email = new Email($command->email);

        if ($this->userRepository->findByEmail($email)) {
            throw new \Exception('User already exists');
        }

        $passwordHash = $this->passwordHasher->hash($command->password);

        $user = new User(
            UserId::generate(),
            $email,
            new Role($command->role),
            $passwordHash
        );
        // Force password change on first login
        $user->requirePasswordChange();

        $this->userRepository->save($user);
    }
}
