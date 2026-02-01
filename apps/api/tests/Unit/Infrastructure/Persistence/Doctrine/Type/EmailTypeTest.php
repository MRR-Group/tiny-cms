<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Auth\ValueObject\Email;
use App\Infrastructure\Persistence\Doctrine\Type\EmailType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EmailTypeTest extends TestCase
{
    private EmailType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        $this->type = (new ReflectionClass(EmailType::class))->newInstanceWithoutConstructor();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testConvertToPHPValueReturnsNullForNull(): void
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    public function testConvertToPHPValueReturnsEmailForString(): void
    {
        $emailStr = "test@example.com";
        $email = $this->type->convertToPHPValue($emailStr, $this->platform);

        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals($emailStr, $email->toString());
    }

    public function testConvertToDatabaseValueReturnsNullForNull(): void
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    public function testConvertToDatabaseValueReturnsStringForEmail(): void
    {
        $email = new Email("test@example.com");
        $result = $this->type->convertToDatabaseValue($email, $this->platform);

        $this->assertSame("test@example.com", $result);
    }

    public function testConvertToDatabaseValueReturnsStringForString(): void
    {
        // This validates the branch $value->toString() : (string)$value
        $result = $this->type->convertToDatabaseValue("string_value", $this->platform);

        $this->assertSame("string_value", $result);
    }

    public function testConvertToDatabaseValueReturnsStringForInt(): void
    {
        $result = $this->type->convertToDatabaseValue(12345, $this->platform);

        $this->assertSame("12345", $result);
    }

    public function testGetName(): void
    {
        $this->assertSame("email", $this->type->getName());
    }
}
