<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Admin;

use App\Application\Auth\Handler\ListUsersHandler;
use App\Application\Auth\Query\ListUsersQuery;
use App\Delivery\Http\Controller\Admin\UserController;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

#[CoversClass(UserController::class)]
class UserControllerTest extends TestCase
{
    private ListUsersHandler&MockObject $handler;
    private UserController $controller;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(ListUsersHandler::class);
        $this->controller = new UserController($this->handler);
    }

    public function testListReturnsUsers(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/admin/users");
        $response = (new ResponseFactory())->createResponse();

        $user = $this->createMock(User::class);
        $user->method("getId")->willReturn(UserId::generate());
        $user->method("getEmail")->willReturn(new Email("test@example.com"));
        $user->method("getRole")->willReturn(Role::editor());

        $this->handler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(ListUsersQuery::class))
            ->willReturn([$user]);

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertCount(1, $body);
        $this->assertEquals("test@example.com", $body[0]["email"]);
        $this->assertEquals("editor", $body[0]["role"]);
    }
}
