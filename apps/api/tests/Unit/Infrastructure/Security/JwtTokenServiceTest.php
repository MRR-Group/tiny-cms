<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use App\Infrastructure\Security\JwtTokenService;
use PHPUnit\Framework\TestCase;

class JwtTokenServiceTest extends TestCase
{
    private JwtTokenService $service;

    protected function tearDown(): void
    {
        \Firebase\JWT\JWT::$timestamp = null;
    }

    protected function setUp(): void
    {
        $_ENV["JWT_SECRET"] = "12345678901234567890123456789012"; // 32 chars
        $clock = $this->createMock(\App\Domain\Shared\Clock\ClockInterface::class);
        $clock->method('now')->willReturn(new \DateTimeImmutable('2024-01-01 12:00:00'));
        $this->service = new JwtTokenService($clock);

        // Mock JWT time
        \Firebase\JWT\JWT::$timestamp = (new \DateTimeImmutable('2024-01-01 12:00:00'))->getTimestamp();
    }

    public function testIssueAndValidateToken(): void
    {
        $user = $this->createMock(User::class);
        $user->method("getId")->willReturn(UserId::generate());
        $user->method("getRole")->willReturn(Role::admin());

        $token = $this->service->issue($user);

        $this->assertNotEmpty($token);

        $claims = $this->service->validate($token);

        $this->assertNotNull($claims);
        $this->assertEquals("tiny-cms", $claims["iss"]);
        $this->assertEquals($user->getId()->toString(), $claims["sub"]);
        $this->assertEquals("admin", $claims["role"]);

        // Kill time mutants
        $now = (new \DateTimeImmutable('2024-01-01 12:00:00'))->getTimestamp();
        $this->assertEquals($now, $claims["iat"]);
        $this->assertEquals($now + 3600, $claims["exp"]);
    }

    public function testValidateReturnsNullForInvalidToken(): void
    {
        $result = $this->service->validate("invalid.token.here");
        $this->assertNull($result);
    }
}
