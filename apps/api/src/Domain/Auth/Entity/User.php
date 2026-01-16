<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entity;

use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;

class User
{
    private bool $mustChangePassword = false;

    public function __construct(
        private readonly UserId $id,
        private Email $email,
        private Role $role,
        private string $passwordHash,
    ) {}

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function mustChangePassword(): bool
    {
        return $this->mustChangePassword;
    }

    public function changePassword(string $newPasswordHash): void
    {
        $this->passwordHash = $newPasswordHash;
        $this->mustChangePassword = false;
    }

    public function requirePasswordChange(): void
    {
        $this->mustChangePassword = true;
    }

    public function updateEmail(Email $email): void
    {
        $this->email = $email;
    }
}
