<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\ReorderSiteSectionsCommand;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class ReorderSiteSectionsHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    public function handle(ReorderSiteSectionsCommand $command): void
    {
        $site = $this->siteRepository->findById(SiteId::fromString($command->siteId));

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        $site->reorderSections($command->sectionIds);
        $this->siteRepository->save($site);
    }
}
