<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\CreateSiteCommand;
use App\Domain\Shared\Clock\ClockInterface;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;

class CreateSiteHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private ClockInterface $clock,
    ) {}

    public function handle(CreateSiteCommand $command): SiteId
    {
        $id = SiteId::generate();
        $type = SiteType::from($command->type);
        $createdAt = $this->clock->now();

        $site = new Site(
            $id,
            $command->name,
            $command->url,
            $type,
            $createdAt,
        );

        $this->siteRepository->save($site);

        return $id;
    }
}
