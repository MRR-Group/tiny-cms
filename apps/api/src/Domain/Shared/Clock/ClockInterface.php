<?php

declare(strict_types=1);

namespace App\Domain\Shared\Clock;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
