<?php

declare(strict_types=1);

namespace App\Application\Site\Command;

class AssignUserToSiteCommand
{
    public function __construct(
        public readonly string $userId,
        public readonly string $siteId,
    ) {
    }
}
