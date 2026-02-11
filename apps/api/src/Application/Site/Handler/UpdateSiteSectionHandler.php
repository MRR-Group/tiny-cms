<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\UpdateSiteSectionCommand;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class UpdateSiteSectionHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(UpdateSiteSectionCommand $command): array
    {
        $site = $this->siteRepository->findById(SiteId::fromString($command->siteId));

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        $section = $site->updateSection($command->sectionId, $command->title, $command->data);
        $this->siteRepository->save($site);

        return $section;
    }
}
