<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Auth\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\Type\UserIdType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class UserIdTypeTest extends TestCase
{
    private UserIdType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(UserIdType::class))->newInstanceWithoutConstructor();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testConvertToPHPValueReturnsNullForNull(): void
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    public function testConvertToPHPValueReturnsUserIdForString(): void
    {
        $uuid = UserId::generate()->toString();
        $userId = $this->type->convertToPHPValue($uuid, $this->platform);

        $this->assertInstanceOf(UserId::class, $userId);
        $this->assertEquals($uuid, $userId->toString());
    }

    public function testConvertToDatabaseValueReturnsNullForNull(): void
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    public function testConvertToDatabaseValueReturnsStringForUserId(): void
    {
        $uuid = UserId::generate();
        $result = $this->type->convertToDatabaseValue($uuid, $this->platform);

        $this->assertSame($uuid->toString(), $result);
    }

    public function testConvertToDatabaseValueReturnsStringForString(): void
    {
        // This validates the branch $value->toString() : (string)$value
        $uuidStr = "7f779b90-1111-2222-3333-444455556666";
        $result = $this->type->convertToDatabaseValue($uuidStr, $this->platform);

        $this->assertSame($uuidStr, $result);
    }

    public function testConvertToDatabaseValueReturnsStringForInt(): void
    {
        $result = $this->type->convertToDatabaseValue(12345, $this->platform);

        $this->assertSame("12345", $result);
    }

    public function testGetName(): void
    {
        $this->assertSame("user_id", $this->type->getName());
    }
}
