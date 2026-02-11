<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\DeleteSiteSectionCommand;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class DeleteSiteSectionHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    public function handle(DeleteSiteSectionCommand $command): void
    {
        $site = $this->siteRepository->findById(SiteId::fromString($command->siteId));

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        $site->removeSection($command->sectionId);
        $this->siteRepository->save($site);
    }
}
