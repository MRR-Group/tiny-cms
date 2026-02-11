<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\AddSiteSectionItemCommand;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class AddSiteSectionItemHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(AddSiteSectionItemCommand $command): array
    {
        $site = $this->siteRepository->findById(SiteId::fromString($command->siteId));

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        $item = $site->addSectionItem($command->sectionId, $command->data);
        $this->siteRepository->save($site);

        return $item;
    }
}
