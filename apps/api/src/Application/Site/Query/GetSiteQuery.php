<?php

declare(strict_types=1);

namespace App\Application\Site\Query;

class GetSiteQuery
{
    public function __construct(
        public string $id,
    ) {}
}
