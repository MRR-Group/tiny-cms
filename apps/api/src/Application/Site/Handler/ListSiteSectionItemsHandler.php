<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Query\ListSiteSectionItemsQuery;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class ListSiteSectionItemsHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(ListSiteSectionItemsQuery $query): array
    {
        $site = $this->siteRepository->findById(SiteId::fromString($query->siteId));

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        return $site->listSectionItems($query->sectionId);
    }
}
