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
    public function testUserCreationAndGetters(): void
    {
        $id = UserId::generate();
        $email = new Email("test@example.com");
        $role = new Role("admin");
        $passwordHash = "hash";

        $user = new User($id, $email, $role, $passwordHash);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($role, $user->getRole());
        $this->assertSame($passwordHash, $user->getPasswordHash());
        $this->assertFalse($user->mustChangePassword());
        $this->assertNull($user->getResetToken());
    }

    public function testUpdateEmail(): void
    {
        $user = new User(UserId::generate(), new Email("old@example.com"), new Role("admin"), "hash");
        $newEmail = new Email("new@example.com");

        $user->updateEmail($newEmail);

        $this->assertEquals($newEmail, $user->getEmail());
    }

    public function testRequirePasswordChange(): void
    {
        $user = new User(UserId::generate(), new Email("test@example.com"), new Role("admin"), "hash");

        $user->requirePasswordChange();

        $this->assertTrue($user->mustChangePassword());
    }

    public function testChangePasswordAndResetTokenClearing(): void
    {
        $user = new User(UserId::generate(), new Email("test@example.com"), new Role("admin"), "hash");
        $user->requirePasswordChange();
        $user->setResetToken("token", new \DateTimeImmutable("+1 hour"));

        $user->changePassword("new_hash");

        $this->assertSame("new_hash", $user->getPasswordHash());
        $this->assertFalse($user->mustChangePassword());
        $this->assertNull($user->getResetToken());
    }

    public function testSetResetTokenAndValidation(): void
    {
        $user = new User(UserId::generate(), new Email("test@example.com"), new Role("admin"), "hash");
        $expiresAt = new \DateTimeImmutable("+1 hour");
        $token = "token123";

        $user->setResetToken($token, $expiresAt);

        $this->assertSame($token, $user->getResetToken());

        $now = new \DateTimeImmutable("now");
        $this->assertTrue($user->isResetTokenValid($token, $now));

        // Exact time should be invalid (it implies stored time > now, so if now == stored, it's not greater)
        // Wait, logic: stored > now.
        // If stored == now, stored > now is False.
        // If mutant is stored >= now, stored == now is True.
        // So checking at exactly expiresAt should distinguish.
        $this->assertFalse($user->isResetTokenValid($token, $expiresAt));

        $future = new \DateTimeImmutable("+2 hours");
        $this->assertFalse($user->isResetTokenValid($token, $future));

        $this->assertFalse($user->isResetTokenValid("wrong_token", $now));
    }

    public function testAddSite(): void
    {
        $user = new User(UserId::generate(), new Email("test@example.com"), new Role("admin"), "hash");
        $site = $this->createMock(\App\Domain\Site\Entity\Site::class);

        $site->expects($this->once())
            ->method('addUser')
            ->with($user);

        $user->addSite($site);

        // Verification of state (collection contains) is tricky with private property and no getter for simple property check without exposing it.
        // But getSites() returns the collection.
        $this->assertTrue($user->getSites()->contains($site));
    }

    public function testRemoveSite(): void
    {
        $user = new User(UserId::generate(), new Email("test@example.com"), new Role("admin"), "hash");
        $site = $this->createMock(\App\Domain\Site\Entity\Site::class);

        $site->expects($this->once())
            ->method('addUser')
            ->with($user);

        $site->expects($this->once())
            ->method('removeUser')
            ->with($user);

        $user->addSite($site);
        $user->removeSite($site);

        $this->assertFalse($user->getSites()->contains($site));
    }
}
