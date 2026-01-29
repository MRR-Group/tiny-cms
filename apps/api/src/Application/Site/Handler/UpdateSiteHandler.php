<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\UpdateSiteCommand;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class UpdateSiteHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    public function handle(UpdateSiteCommand $command): void
    {
        $siteId = SiteId::fromString($command->siteId);
        $site = $this->siteRepository->findById($siteId);

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        $site->updateName($command->name);
        $site->updateUrl($command->url);
        $site->updateType($command->type);

        $this->siteRepository->save($site);
    }
}
