<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Middleware;

use App\Delivery\Http\Middleware\SiteAccessMiddleware;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\DispatcherInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Routing\RouteContext;
use Slim\Routing\RoutingResults;

class SiteAccessMiddlewareTest extends TestCase
{
    public function testReturns401WhenUserIdMissing(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $middleware = new SiteAccessMiddleware($repository);
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/sites/id/sections")
            ->withAttribute("id", SiteId::generate()->toString());
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $middleware->process($request, $handler);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testReturns403WhenUserNotAssignedToSite(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $middleware = new SiteAccessMiddleware($repository);

        $site = $this->createMock(Site::class);
        $site->method("hasAssignedUser")->with("00000000-0000-0000-0000-000000000123")->willReturn(false);
        $repository->method("findById")->willReturn($site);

        $request = (new ServerRequestFactory())->createServerRequest("GET", "/sites/id/sections")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("userId", "00000000-0000-0000-0000-000000000123");
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $middleware->process($request, $handler);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString("Forbidden", (string)$response->getBody());
    }

    public function testPassesWhenUserAssignedToSite(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $middleware = new SiteAccessMiddleware($repository);

        $site = $this->createMock(Site::class);
        $site->method("hasAssignedUser")->with("00000000-0000-0000-0000-000000000123")->willReturn(true);
        $repository->method("findById")->willReturn($site);

        $request = (new ServerRequestFactory())->createServerRequest("GET", "/sites/id/sections")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("userId", "00000000-0000-0000-0000-000000000123");

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method("handle")->willReturn((new ResponseFactory())->createResponse());

        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testReturns400WhenSiteIdIsMissingAndRouteHasNoId(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $middleware = new SiteAccessMiddleware($repository);
        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/sites/sections")
            ->withAttribute("userId", "00000000-0000-0000-0000-000000000123")
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->createMock(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, new RoutingResults(
                $this->createMock(DispatcherInterface::class),
                "GET",
                "/sites/sections",
                RoutingResults::FOUND,
            ));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString("Site ID is required", (string)$response->getBody());
    }

    public function testUsesRouteArgumentWhenSiteIdAttributeMissing(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $middleware = new SiteAccessMiddleware($repository);

        $site = $this->createMock(Site::class);
        $site->expects($this->once())->method("hasAssignedUser")->with("00000000-0000-0000-0000-000000000123")->willReturn(true);
        $repository->expects($this->once())->method("findById")->willReturn($site);

        $route = $this->createMock(RouteInterface::class);
        $route->expects($this->once())->method("getArgument")->with("id")->willReturn(SiteId::generate()->toString());

        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/sites/fallback-id/sections")
            ->withAttribute(RouteContext::ROUTE, $route)
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->createMock(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, new RoutingResults(
                $this->createMock(DispatcherInterface::class),
                "GET",
                "/sites/fallback-id/sections",
                RoutingResults::FOUND,
            ))
            ->withAttribute("userId", "00000000-0000-0000-0000-000000000123");

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method("handle")->willReturn((new ResponseFactory())->createResponse());

        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testTrimsRouteArgumentWhenResolvingSiteId(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $middleware = new SiteAccessMiddleware($repository);

        $site = $this->createMock(Site::class);
        $site->expects($this->once())->method("hasAssignedUser")->willReturn(true);
        $repository->expects($this->once())->method("findById")->with($this->callback(
            static fn(SiteId $id): bool => $id->toString() === "00000000-0000-0000-0000-000000000111",
        ))->willReturn($site);

        $route = $this->createMock(RouteInterface::class);
        $route->expects($this->once())->method("getArgument")->with("id")->willReturn(" 00000000-0000-0000-0000-000000000111 ");

        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/sites/id/sections")
            ->withAttribute(RouteContext::ROUTE, $route)
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->createMock(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, new RoutingResults(
                $this->createMock(DispatcherInterface::class),
                "GET",
                "/sites/id/sections",
                RoutingResults::FOUND,
            ))
            ->withAttribute("userId", "00000000-0000-0000-0000-000000000123")
            ->withAttribute("id", "");

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method("handle")->willReturn((new ResponseFactory())->createResponse());

        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testReturns404WhenSiteDoesNotExist(): void
    {
        $repository = $this->createMock(SiteRepositoryInterface::class);
        $repository->expects($this->once())->method("findById")->willReturn(null);
        $middleware = new SiteAccessMiddleware($repository);

        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/sites/id/sections")
            ->withAttribute("id", SiteId::generate()->toString())
            ->withAttribute("userId", "00000000-0000-0000-0000-000000000123");

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString("Site not found", (string)$response->getBody());
    }
}
