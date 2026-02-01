<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\CreateSiteCommand;
use App\Domain\Shared\Clock\ClockInterface;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;

use App\Domain\Shared\Util\UrlNormalizer;

class CreateSiteHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private ClockInterface $clock,
        private UrlNormalizer $urlNormalizer,
    ) {}

    public function handle(CreateSiteCommand $command): SiteId
    {
        $id = SiteId::generate();
        $type = SiteType::from($command->type);
        $createdAt = $this->clock->now();

        $url = $this->urlNormalizer->normalize($command->url);

        if ($this->siteRepository->findByUrl($url)) {
            throw new \InvalidArgumentException("Site with URL '{$url}' already exists");
        }

        $site = new Site(
            $id,
            $command->name,
            $url,
            $type,
            $createdAt,
        );

        $this->siteRepository->save($site);

        return $id;
    }
}
