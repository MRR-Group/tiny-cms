<?php

declare(strict_types=1);

namespace App\Application\Site\Command;

class AddSiteSectionCommand
{
    public function __construct(
        public readonly string $siteId,
        public readonly string $type,
        public readonly string $title,
    ) {}
}
