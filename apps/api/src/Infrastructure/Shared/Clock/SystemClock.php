<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Clock;

use App\Domain\Shared\Clock\ClockInterface;

class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
