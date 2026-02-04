<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Shared\Clock;

use App\Infrastructure\Shared\Clock\SystemClock;
use PHPUnit\Framework\TestCase;

class SystemClockTest extends TestCase
{
    public function testNowReturnsCurrentTime(): void
    {
        $clock = new SystemClock();
        $now = $clock->now();

        $this->assertInstanceOf(\DateTimeImmutable::class, $now);
        $this->assertLessThanOrEqual(new \DateTimeImmutable(), $now);
    }
}
