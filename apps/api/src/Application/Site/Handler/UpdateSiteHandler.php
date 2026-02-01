<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\UpdateSiteCommand;
use App\Domain\Shared\Util\UrlNormalizer;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class UpdateSiteHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
        private readonly UrlNormalizer $urlNormalizer,
    ) {}

    public function handle(UpdateSiteCommand $command): void
    {
        $siteId = SiteId::fromString($command->siteId);
        $site = $this->siteRepository->findById($siteId);

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        $url = $this->urlNormalizer->normalize($command->url);
        $existingSite = $this->siteRepository->findByUrl($url);

        if ($existingSite !== null && !$existingSite->getId()->equals($siteId)) {
            throw new \InvalidArgumentException("Site with URL '{$url}' already exists");
        }

        $site->updateName($command->name);
        $site->updateUrl($url);
        $site->updateType($command->type);

        $this->siteRepository->save($site);
    }
}
