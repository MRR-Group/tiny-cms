<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\UserId;
use App\Domain\Auth\ValueObject\Role;
use App\Infrastructure\Security\JwtTokenService;
use PHPUnit\Framework\TestCase;

class JwtTokenServiceTest extends TestCase
{
    private JwtTokenService $service;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = '12345678901234567890123456789012'; // 32 chars
        $this->service = new JwtTokenService();
    }

    public function testIssueAndValidateToken(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(UserId::generate());
        $user->method('getRole')->willReturn(Role::admin());

        $token = $this->service->issue($user);

        $this->assertNotEmpty($token);

        $claims = $this->service->validate($token);

        $this->assertNotNull($claims);
        $this->assertEquals('tiny-cms', $claims['iss']);
        $this->assertEquals('admin', $claims['role']);
    }

    public function testValidateReturnsNullForInvalidToken(): void
    {
        $result = $this->service->validate('invalid.token.here');
        $this->assertNull($result);
    }
}
