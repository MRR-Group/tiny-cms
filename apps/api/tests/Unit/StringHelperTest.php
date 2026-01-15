<?php

declare(strict_types=1);

namespace TinyCMS\Api\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TinyCMS\Api\Support\StringHelper;

final class StringHelperTest extends TestCase
{
    public function testItGreetsWithName(): void
    {
        self::assertSame('Hello CMS', StringHelper::greet('CMS'));
    }
}
