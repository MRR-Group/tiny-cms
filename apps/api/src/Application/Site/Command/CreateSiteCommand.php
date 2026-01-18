<?php

declare(strict_types=1);

namespace App\Application\Site\Command;

class CreateSiteCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly string $type,
    ) {
    }
}
