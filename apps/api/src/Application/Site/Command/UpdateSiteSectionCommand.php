<?php

declare(strict_types=1);

namespace App\Application\Site\Command;

class UpdateSiteSectionCommand
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public readonly string $siteId,
        public readonly string $sectionId,
        public readonly string $title,
        public readonly array $data,
    ) {}
}
