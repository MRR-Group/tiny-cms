<?php

declare(strict_types=1);

namespace App\Application\Site\Query;

class ListSiteSectionsQuery
{
    public function __construct(
        public readonly string $siteId,
    ) {}
}
