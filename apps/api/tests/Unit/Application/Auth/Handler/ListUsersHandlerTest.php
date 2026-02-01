<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth\Handler;

use App\Application\Auth\Handler\ListUsersHandler;
use App\Application\Auth\Query\ListUsersQuery;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListUsersHandler::class)]
class ListUsersHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $repository;
    private ListUsersHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $this->handler = new ListUsersHandler($this->repository);
    }

    public function testHandleReturnsUsers(): void
    {
        $query = new ListUsersQuery();
        $user = $this->createMock(User::class);

        $this->repository->expects($this->once())
            ->method("findAll")
            ->willReturn([$user]);

        $result = $this->handler->handle($query);

        $this->assertCount(1, $result);
        $this->assertSame($user, $result[0]);
    }
}
