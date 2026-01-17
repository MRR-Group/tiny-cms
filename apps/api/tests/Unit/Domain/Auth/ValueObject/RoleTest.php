<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Auth\ValueObject;

use App\Domain\Auth\ValueObject\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testCanCreateAdminRole(): void
    {
        $role = Role::admin();
        $this->assertEquals(Role::ADMIN, $role->toString());
    }

    public function testCanCreateEditorRole(): void
    {
        $role = Role::editor();
        $this->assertEquals(Role::EDITOR, $role->toString());
    }

    public function testThrowsExceptionForInvalidRole(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // Using reflection to bypass constructor visibility if needed, or just new Role if public
        new Role('invalid_role');
    }
}
