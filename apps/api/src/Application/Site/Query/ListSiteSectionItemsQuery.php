<?php

declare(strict_types=1);

namespace App\Application\Site\Query;

class ListSiteSectionItemsQuery
{
    public function __construct(
        public readonly string $siteId,
        public readonly string $sectionId,
    ) {}
}
