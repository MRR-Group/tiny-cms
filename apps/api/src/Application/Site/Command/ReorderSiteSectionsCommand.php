<?php

declare(strict_types=1);

namespace App\Application\Site\Command;

class ReorderSiteSectionsCommand
{
    /**
     * @param array<int, string> $sectionIds
     */
    public function __construct(
        public readonly string $siteId,
        public readonly array $sectionIds,
    ) {}
}
