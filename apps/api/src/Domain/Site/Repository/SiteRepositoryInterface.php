<?php

declare(strict_types=1);

namespace App\Domain\Site\Repository;

use App\Domain\Site\Entity\Site;
use App\Domain\Site\ValueObject\SiteId;

interface SiteRepositoryInterface
{
    public function save(Site $site): void;
    public function findById(SiteId $id): ?Site;
    /** @return Site[] */
    public function findAll(): array;
}
