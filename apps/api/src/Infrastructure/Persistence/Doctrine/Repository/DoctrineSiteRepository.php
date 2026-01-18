<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineSiteRepository implements SiteRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function save(Site $site): void
    {
        $this->entityManager->persist($site);
        $this->entityManager->flush();
    }

    public function findById(SiteId $id): ?Site
    {
        return $this->entityManager->find(Site::class, $id);
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(Site::class)->findAll();
    }
}
