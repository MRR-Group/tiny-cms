<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Query\ListSitesQuery;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;

class ListSitesHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {}

    /**
     * @return array<Site>
     */
    public function handle(ListSitesQuery $query): array
    {
        return $this->siteRepository->findAll();
    }
}
