<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Query\ListSiteSectionsQuery;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class ListSiteSectionsHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(ListSiteSectionsQuery $query): array
    {
        $site = $this->siteRepository->findById(SiteId::fromString($query->siteId));

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        return $site->getSections();
    }
}
