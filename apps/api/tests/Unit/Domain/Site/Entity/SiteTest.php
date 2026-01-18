<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Site\Entity;

use App\Domain\Auth\Entity\User;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\TestCase;

class SiteTest extends TestCase
{
    public function testRemoveUserRemovesUserFromSiteAndSiteFromUser(): void
    {
        $site = new Site(
            SiteId::generate(),
            'Test Site',
            'http://example.com',
            SiteType::STATIC ,
            new \DateTimeImmutable()
        );
        $user = $this->createMock(User::class);

        // Expect removeSite to be called on user when removed from site
        $user->expects($this->once())
            ->method('removeSite')
            ->with($site);

        // We need to simulate the collection behavior if possible, but since it's a Doctrine Collection interface,
        // and initialized in constructor, we might need a real User or mock the collection.
        // However, Site initializes ArrayCollection.
        // Let's use a real User entity for this test to ensure bidirectional relationship works, 
        // or just rely on the fact that we can add the mock to the collection.

        // Since User::removeSite is expected, we need the site to actually contain the user first.

        // But Site::addUser calls $user->addSite($this).
        $user->expects($this->once())
            ->method('addSite')
            ->with($site);

        $site->addUser($user);

        $site->removeUser($user);

        // Verify user is not in the collection
        $this->assertFalse($site->getUsers()->contains($user));
    }

    public function testGetters(): void
    {
        $id = SiteId::generate();
        $name = 'Name';
        $url = 'Url';
        $type = SiteType::STATIC;
        $createdAt = new \DateTimeImmutable();

        $site = new Site($id, $name, $url, $type, $createdAt);

        $this->assertSame($id, $site->getId());
        $this->assertSame($name, $site->getName());
        $this->assertSame($url, $site->getUrl());
        $this->assertSame($type, $site->getType());
        $this->assertSame($createdAt, $site->getCreatedAt());
    }
}
