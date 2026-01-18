<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Site\ValueObject\SiteType;
use App\Infrastructure\Persistence\Doctrine\Type\SiteTypeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

class SiteTypeTypeTest extends TestCase
{
    public function testConvertToDatabaseValueReturnsStringForEnum(): void
    {
        $type = new SiteTypeType();
        $platform = $this->createMock(AbstractPlatform::class);

        $this->assertEquals("dynamic", $type->convertToDatabaseValue(SiteType::DYNAMIC, $platform));
    }

    public function testConvertToDatabaseValueReturnsValueForNonEnum(): void
    {
        $type = new SiteTypeType();
        $platform = $this->createMock(AbstractPlatform::class);

        $this->assertEquals("some_string", $type->convertToDatabaseValue("some_string", $platform));
        $this->assertEquals(123, $type->convertToDatabaseValue(123, $platform)); // If no cast
    }

    public function testConvertToPHPValueReturnsEnum(): void
    {
        $type = new SiteTypeType();
        $platform = $this->createMock(AbstractPlatform::class);

        $result = $type->convertToPHPValue("dynamic", $platform);
        $this->assertEquals(SiteType::DYNAMIC, $result);
    }

    public function testGetNameReturnsName(): void
    {
        $type = new SiteTypeType();
        $this->assertEquals("site_type", $type->getName());
    }
}
