<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Auth\ValueObject;

use App\Domain\Auth\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = new Email("test@example.com");
        $this->assertEquals("test@example.com", $email->toString());
        $this->assertEquals("test@example.com", (string)$email);
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Email("invalid-email");
    }

    public function testEquality(): void
    {
        $email1 = new Email("a@b.c");
        $email2 = new Email("a@b.c");
        $email3 = new Email("x@y.z");

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    public function testFactoryMethod(): void
    {
        $email = Email::fromString("test@example.com");
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals("test@example.com", $email->toString());
    }
}
