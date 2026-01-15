<?php

declare(strict_types=1);

namespace TinyCMS\Api\Support;

final class StringHelper
{
    public static function greet(string $name): string
    {
        return sprintf('Hello %s', $name);
    }
}
