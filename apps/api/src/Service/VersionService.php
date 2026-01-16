<?php

declare(strict_types=1);

namespace App\Service;

final class VersionService
{
    private const VERSION = "1.0.0";

    public function getVersion(): string
    {
        return self::VERSION;
    }

    public function getMajorVersion(): int
    {
        $parts = explode(".", self::VERSION);

        return (int)$parts[0];
    }

    public function getMinorVersion(): int
    {
        $parts = explode(".", self::VERSION);

        return (int)($parts[1] ?? 0);
    }

    public function getPatchVersion(): int
    {
        $parts = explode(".", self::VERSION);

        return (int)($parts[2] ?? 0);
    }
}
