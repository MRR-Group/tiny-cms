<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

class UuidTest extends TestCase
{
    private const VALID_UUID = "f47ac10b-58cc-4372-a567-0e02b2c3d479";

    public function testCanBeCreatedFromValidString(): void
    {
        $uuid = ConcreteUuid::fromString(self::VALID_UUID);
        $this->assertEquals(self::VALID_UUID, $uuid->toString());
        $this->assertEquals(self::VALID_UUID, (string)$uuid);
    }

    public function testThrowsExceptionForInvalidUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ConcreteUuid::fromString("invalid-uuid");
    }

    public function testCanGenerateRandomUuid(): void
    {
        $uuid = ConcreteUuid::generate();
        $this->assertNotEmpty($uuid->toString());
    }

    public function testEquality(): void
    {
        $uuid1 = ConcreteUuid::fromString(self::VALID_UUID);
        $uuid2 = ConcreteUuid::fromString(self::VALID_UUID);
        $uuid3 = ConcreteUuid::generate();

        $this->assertTrue($uuid1->equals($uuid2));
        $this->assertFalse($uuid1->equals($uuid3));
    }
}

class ConcreteUuid extends Uuid
{
}
