<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Resource;

use App\Delivery\Http\Resource\UserResource;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

class UserResourceTest extends TestCase
{
    public function testToArrayConvertsUserToArray(): void
    {
        $userId = UserId::generate();
        $email = Email::fromString("test@example.com");
        $role = Role::admin();

        $user = $this->createMock(User::class);
        $user->method("getId")->willReturn($userId);
        $user->method("getEmail")->willReturn($email);
        $user->method("getRole")->willReturn($role);

        $result = UserResource::toArray($user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("email", $result);
        $this->assertArrayHasKey("role", $result);
        $this->assertSame((string)$userId, $result["id"]);
        $this->assertSame((string)$email, $result["email"]);
        $this->assertSame($role->toString(), $result["role"]);
    }

    public function testCollectionToArrayConvertsMultipleUsers(): void
    {
        $user1 = $this->createMock(User::class);
        $user1->method("getId")->willReturn(UserId::generate());
        $user1->method("getEmail")->willReturn(Email::fromString("user1@example.com"));
        $user1->method("getRole")->willReturn(Role::admin());

        $user2 = $this->createMock(User::class);
        $user2->method("getId")->willReturn(UserId::generate());
        $user2->method("getEmail")->willReturn(Email::fromString("user2@example.com"));
        $user2->method("getRole")->willReturn(Role::editor());

        $users = [$user1, $user2];

        $result = UserResource::collectionToArray($users);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey("id", $result[0]);
        $this->assertArrayHasKey("email", $result[0]);
        $this->assertArrayHasKey("role", $result[0]);
        $this->assertArrayHasKey("id", $result[1]);
        $this->assertArrayHasKey("email", $result[1]);
        $this->assertArrayHasKey("role", $result[1]);
    }

    public function testCollectionToArrayWithEmptyArray(): void
    {
        $result = UserResource::collectionToArray([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
