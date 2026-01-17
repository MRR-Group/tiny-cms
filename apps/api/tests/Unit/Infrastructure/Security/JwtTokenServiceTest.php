<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use App\Domain\Shared\Clock\ClockInterface;
use App\Infrastructure\Security\JwtTokenService;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;

class JwtTokenServiceTest extends TestCase
{
    private JwtTokenService $service;

    protected function setUp(): void
    {
        $key = "12345678901234567890123456789012"; // 32 chars
        $clock = $this->createMock(ClockInterface::class);
        $clock->method("now")->willReturn(new \DateTimeImmutable("2024-01-01 12:00:00"));
        $this->service = new JwtTokenService($clock, $key);

        // Mock JWT time
        JWT::$timestamp = (new \DateTimeImmutable("2024-01-01 12:00:00"))->getTimestamp();
    }

    protected function tearDown(): void
    {
        JWT::$timestamp = null;
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
        $now = (new \DateTimeImmutable("2024-01-01 12:00:00"))->getTimestamp();
        $this->assertEquals($now, $claims["iat"]);
        $this->assertEquals($now + 3600, $claims["exp"]);
    }

    public function testValidateReturnsNullForInvalidToken(): void
    {
        $result = $this->service->validate("invalid.token.here");
        $this->assertNull($result);
    }
}
