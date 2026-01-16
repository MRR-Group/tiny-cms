<?php

declare(strict_types=1);

namespace App\Service;

final class VersionService
{
    private const DEFAULT_VERSION = "1.0.0";

    /** @var array<int, string> */
    private array $parts;

    public function __construct(
        private readonly string $version = self::DEFAULT_VERSION,
    ) {
        $this->parts = explode(".", $this->version);
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getMajorVersion(): int
    {
        return (int) ($this->parts[0] ?? 0);
    }

    public function getMinorVersion(): int
    {
        return (int) ($this->parts[1] ?? 0);
    }

    public function getPatchVersion(): int
    {
        return (int) ($this->parts[2] ?? 0);
    }
}
