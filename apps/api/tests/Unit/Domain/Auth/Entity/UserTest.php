<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Auth\Entity;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreationAndAccessors(): void
    {
        $id = UserId::generate();
        $email = new Email('test@example.com');
        $role = Role::admin();
        $hash = 'hashed_password';

        $user = new User($id, $email, $role, $hash);

        $this->assertSame($id, $user->getId());
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($role, $user->getRole());
        $this->assertSame($hash, $user->getPasswordHash());
        $this->assertFalse($user->mustChangePassword());
    }

    public function testChangePasswordFlow(): void
    {
        $user = new User(UserId::generate(), new Email('test@example.com'), Role::admin(), 'hash');

        $user->requirePasswordChange();
        $this->assertTrue($user->mustChangePassword());

        $user->changePassword('new_hash');
        $this->assertEquals('new_hash', $user->getPasswordHash());
        $this->assertFalse($user->mustChangePassword());
    }

    public function testUpdateEmail(): void
    {
        $user = new User(UserId::generate(), new Email('old@example.com'), Role::admin(), 'hash');
        $newEmail = new Email('new@example.com');

        $user->updateEmail($newEmail);

        $this->assertSame($newEmail, $user->getEmail());
    }
}
