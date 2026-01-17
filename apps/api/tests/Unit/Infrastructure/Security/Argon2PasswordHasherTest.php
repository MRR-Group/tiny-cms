<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Security;

use App\Infrastructure\Security\Argon2PasswordHasher;
use PHPUnit\Framework\TestCase;

class Argon2PasswordHasherTest extends TestCase
{
    private Argon2PasswordHasher $hasher;

    protected function setUp(): void
    {
        $this->hasher = new Argon2PasswordHasher();
    }

    public function testHashAndVerify(): void
    {
        $password = "secret123";
        $hash = $this->hasher->hash($password);

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertTrue($this->hasher->verify($password, $hash));
        $this->assertFalse($this->hasher->verify("wrong", $hash));
    }
}
