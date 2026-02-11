<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\UpdateSiteSectionItemCommand;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class UpdateSiteSectionItemHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(UpdateSiteSectionItemCommand $command): array
    {
        $site = $this->siteRepository->findById(SiteId::fromString($command->siteId));

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        $item = $site->updateSectionItem($command->sectionId, $command->itemId, $command->data);
        $this->siteRepository->save($site);

        return $item;
    }
}
