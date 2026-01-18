<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Handler\GetUserSitesHandler;
use App\Application\Site\Query\GetUserSitesQuery;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use App\Domain\Auth\ValueObject\UserId;
use App\Domain\Site\Entity\Site;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;

class GetUserSitesHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private GetUserSitesHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->handler = new GetUserSitesHandler($this->userRepository);
    }

    public function testHandleReturnsSites(): void
    {
        $userId = UserId::generate();
        $query = new GetUserSitesQuery($userId->toString());

        $user = $this->createMock(User::class);
        $site = $this->createMock(Site::class);

        $this->userRepository->method('findById')->willReturn($user);

        // Mock getSites returning collection
        $user->method('getSites')->willReturn(new ArrayCollection([$site]));

        $result = $this->handler->handle($query);

        $this->assertCount(1, $result);
        $this->assertSame($site, $result[0]);
    }

    public function testHandleReturnsEmptyIfUserNotFound(): void
    {
        $userId = UserId::generate();
        $query = new GetUserSitesQuery($userId->toString());

        $this->userRepository->method('findById')->willReturn(null);

        $result = $this->handler->handle($query);

        $this->assertEmpty($result);
    }
}
