<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Site\ValueObject\SiteId;
use App\Infrastructure\Persistence\Doctrine\Type\SiteIdType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SiteIdTypeTest extends TestCase
{
    private SiteIdType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(SiteIdType::class))->newInstanceWithoutConstructor();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testConvertToPHPValueReturnsNullForNull(): void
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    public function testConvertToPHPValueReturnsSiteIdForString(): void
    {
        $uuid = SiteId::generate()->toString();
        $siteId = $this->type->convertToPHPValue($uuid, $this->platform);

        $this->assertInstanceOf(SiteId::class, $siteId);
        $this->assertEquals($uuid, $siteId->toString());
    }

    public function testConvertToDatabaseValueReturnsNullForNull(): void
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    public function testConvertToDatabaseValueReturnsStringForSiteId(): void
    {
        $uuid = SiteId::generate();
        $result = $this->type->convertToDatabaseValue($uuid, $this->platform);

        $this->assertSame($uuid->toString(), $result);
    }

    public function testConvertToDatabaseValueReturnsStringForString(): void
    {
        $uuidStr = "7f779b90-1111-2222-3333-444455556666";
        $result = $this->type->convertToDatabaseValue($uuidStr, $this->platform);

        $this->assertSame($uuidStr, $result);
    }

    public function testGetName(): void
    {
        $this->assertSame("site_id", $this->type->getName());
    }
}
