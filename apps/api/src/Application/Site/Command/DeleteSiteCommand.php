<?php

declare(strict_types=1);

namespace App\Application\Site\Command;

class DeleteSiteCommand
{
    public function __construct(
        public readonly string $siteId,
    ) {}
}
