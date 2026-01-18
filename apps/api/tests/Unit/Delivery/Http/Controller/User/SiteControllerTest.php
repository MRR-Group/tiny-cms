<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\User;

use App\Application\Site\Handler\GetUserSitesHandler;
use App\Application\Site\Query\GetUserSitesQuery;
use App\Delivery\Http\Controller\User\SiteController;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class SiteControllerTest extends TestCase
{
    private GetUserSitesHandler&MockObject $handler;
    private SiteController $controller;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(GetUserSitesHandler::class);
        $this->controller = new SiteController($this->handler);
    }

    public function testListAssignedReturns200AndData(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/sites')
            ->withAttribute('userId', 'user-uuid');
        $response = (new ResponseFactory())->createResponse();

        $site = $this->createMock(Site::class);
        $site->method('getId')->willReturn(SiteId::generate());
        $site->method('getName')->willReturn('My Site');
        $site->method('getUrl')->willReturn('url');
        $site->method('getType')->willReturn(SiteType::STATIC);
        $site->method('getCreatedAt')->willReturn(new \DateTimeImmutable());

        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(fn(GetUserSitesQuery $q) => $q->userId === 'user-uuid'))
            ->willReturn([$site]);

        $result = $this->controller->listAssigned($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertCount(1, $body);
    }

    public function testListAssignedReturns400IfUserIdMissing(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/sites');
        // No attribute
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->listAssigned($request, $response, []);

        $this->assertEquals(401, $result->getStatusCode());
        $this->assertStringContainsString('User ID not found', (string) $result->getBody());
    }
}
