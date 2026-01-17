<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Auth;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $id = UserId::generate();
        $email = new Email("test@example.com");
        $role = Role::admin();
        $user = new User($id, $email, $role, "hashed_password");

        $this->assertTrue($user->getId()->equals($id));
        $this->assertEquals("test@example.com", $user->getEmail()->toString());
        $this->assertEquals("admin", $user->getRole()->toString());
        $this->assertFalse($user->mustChangePassword());
    }

    public function testRequirePasswordChange(): void
    {
        $user = new User(UserId::generate(), new Email("a@b.c"), Role::editor(), "hash");

        $user->requirePasswordChange();
        $this->assertTrue($user->mustChangePassword());

        $user->changePassword("new_hash");
        $this->assertFalse($user->mustChangePassword());
        $this->assertEquals("new_hash", $user->getPasswordHash());
    }
}
