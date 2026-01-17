<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Auth\ValueObject\Role;
use App\Infrastructure\Persistence\Doctrine\Type\RoleType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RoleTypeTest extends TestCase
{
    private RoleType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(RoleType::class))->newInstanceWithoutConstructor();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testConvertToPHPValueReturnsNullForNull(): void
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    public function testConvertToPHPValueReturnsRoleForString(): void
    {
        $roleStr = "admin";
        $role = $this->type->convertToPHPValue($roleStr, $this->platform);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($roleStr, $role->toString());
    }

    public function testConvertToDatabaseValueReturnsNullForNull(): void
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    public function testConvertToDatabaseValueReturnsStringForRole(): void
    {
        $role = Role::admin();
        $result = $this->type->convertToDatabaseValue($role, $this->platform);

        $this->assertSame("admin", $result);
    }

    public function testConvertToDatabaseValueReturnsStringForString(): void
    {
        // This validates the branch $value->toString() : (string)$value
        $result = $this->type->convertToDatabaseValue("admin", $this->platform);

        $this->assertSame("admin", $result);
    }

    public function testConvertToDatabaseValueReturnsStringForInt(): void
    {
        $result = $this->type->convertToDatabaseValue(123, $this->platform);

        $this->assertSame("123", $result);
    }

    public function testGetName(): void
    {
        $this->assertSame("role", $this->type->getName());
    }
}
