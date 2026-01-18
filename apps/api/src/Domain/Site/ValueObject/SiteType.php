<?php

declare(strict_types=1);

namespace App\Domain\Site\ValueObject;

enum SiteType: string
{
    case STATIC = "static";
    case DYNAMIC = "dynamic";
}
