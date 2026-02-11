<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\DeleteSiteSectionItemCommand;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class DeleteSiteSectionItemHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    public function handle(DeleteSiteSectionItemCommand $command): void
    {
        $site = $this->siteRepository->findById(SiteId::fromString($command->siteId));

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        $site->removeSectionItem($command->sectionId, $command->itemId);
        $this->siteRepository->save($site);
    }
}
