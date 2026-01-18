<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entity;

use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use App\Domain\Site\Entity\Site;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class User
{
    private bool $mustChangePassword = false;
    private ?string $resetToken = null;
    private ?\DateTimeImmutable $resetTokenExpiresAt = null;

    /** @var Collection<int, Site> */
    private Collection $sites;

    public function __construct(
        private readonly UserId $id,
        private Email $email,
        private Role $role,
        private string $passwordHash,
    ) {
        $this->sites = new ArrayCollection();
    }

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
        $this->resetToken = null;
        $this->resetTokenExpiresAt = null;
    }

    public function requirePasswordChange(): void
    {
        $this->mustChangePassword = true;
    }

    public function updateEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function setResetToken(string $token, \DateTimeImmutable $expiresAt): void
    {
        $this->resetToken = $token;
        $this->resetTokenExpiresAt = $expiresAt;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function isResetTokenValid(string $token, \DateTimeImmutable $now): bool
    {
        return $this->resetToken === $token && $this->resetTokenExpiresAt > $now;
    }

    /**
     * @return Collection<int, Site>
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(Site $site): void
    {
        if (!$this->sites->contains($site)) {
            $this->sites->add($site);
            $site->addUser($this);
        }
    }

    public function removeSite(Site $site): void
    {
        if ($this->sites->removeElement($site)) {
            $site->removeUser($this);
        }
    }
}
