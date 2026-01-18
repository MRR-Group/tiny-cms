<?php

declare(strict_types=1);

namespace App\Domain\Site\Entity;

use App\Domain\Auth\Entity\User;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Site
{
    /** @var Collection<int, User> */
    private Collection $users;

    public function __construct(
        private readonly SiteId $id,
        private string $name,
        private string $url,
        private SiteType $type,
        private \DateTimeImmutable $createdAt,
    ) {
        $this->users = new ArrayCollection();
    }

    public function getId(): SiteId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getType(): SiteType
    {
        return $this->type;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addSite($this);
        }
    }

    public function removeUser(User $user): void
    {
        if ($this->users->removeElement($user)) {
            $user->removeSite($this);
        }
    }
}
