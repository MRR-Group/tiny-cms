<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\DeleteSiteCommand;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class DeleteSiteHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    public function handle(DeleteSiteCommand $command): void
    {
        $siteId = SiteId::fromString($command->siteId);
        $site = $this->siteRepository->findById($siteId);

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        if ($site->getEditorCount() > 0) {
            throw new \InvalidArgumentException("Cannot delete site with assigned editors");
        }

        $this->siteRepository->delete($site);
    }
}
