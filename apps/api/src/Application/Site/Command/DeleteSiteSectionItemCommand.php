<?php

declare(strict_types=1);

namespace App\Application\Site\Command;

class DeleteSiteSectionItemCommand
{
    public function __construct(
        public readonly string $siteId,
        public readonly string $sectionId,
        public readonly string $itemId,
    ) {}
}
