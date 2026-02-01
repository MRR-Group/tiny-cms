<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Query\GetSiteQuery;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class GetSiteHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {}

    public function handle(GetSiteQuery $query): Site
    {
        $site = $this->siteRepository->findById(new SiteId($query->id));

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        return $site;
    }
}
