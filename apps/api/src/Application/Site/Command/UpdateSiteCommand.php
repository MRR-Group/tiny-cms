<?php

declare(strict_types=1);

namespace App\Application\Site\Command;

use App\Domain\Site\ValueObject\SiteType;

class UpdateSiteCommand
{
    public function __construct(
        public readonly string $siteId,
        public readonly string $name,
        public readonly string $url,
        public readonly SiteType $type,
    ) {}
}
