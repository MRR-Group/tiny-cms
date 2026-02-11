<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Admin;

use App\Application\Site\Handler\AddSiteSectionHandler;
use App\Application\Site\Handler\AddSiteSectionItemHandler;
use App\Application\Site\Handler\AssignUserToSiteHandler;
use App\Application\Site\Handler\CreateSiteHandler;
use App\Application\Site\Handler\DeleteSiteHandler;
use App\Application\Site\Handler\DeleteSiteSectionHandler;
use App\Application\Site\Handler\DeleteSiteSectionItemHandler;
use App\Application\Site\Handler\GetSiteHandler;
use App\Application\Site\Handler\ListSiteSectionItemsHandler;
use App\Application\Site\Handler\ListSiteSectionsHandler;
use App\Application\Site\Handler\ListSitesHandler;
use App\Application\Site\Handler\ReorderSiteSectionsHandler;
use App\Application\Site\Handler\UnassignUserFromSiteHandler;
use App\Application\Site\Handler\UpdateSiteHandler;
use App\Application\Site\Handler\UpdateSiteSectionHandler;
use App\Application\Site\Handler\UpdateSiteSectionItemHandler;
use App\Delivery\Http\Controller\Admin\SiteController;
use App\Delivery\Http\Controller\Admin\SiteSectionActionHandlers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slim\Interfaces\DispatcherInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Routing\RouteContext;
use Slim\Routing\RoutingResults;

class SiteControllerSectionEndpointsTest extends TestCase
{
    private AddSiteSectionHandler&MockObject $addSectionHandler;
    private AddSiteSectionItemHandler&MockObject $addSectionItemHandler;
    private ListSiteSectionsHandler&MockObject $listSectionsHandler;
    private ListSiteSectionItemsHandler&MockObject $listSectionItemsHandler;
    private UpdateSiteSectionHandler&MockObject $updateSectionHandler;
    private UpdateSiteSectionItemHandler&MockObject $updateSectionItemHandler;
    private DeleteSiteSectionHandler&MockObject $deleteSectionHandler;
    private DeleteSiteSectionItemHandler&MockObject $deleteSectionItemHandler;
    private SiteController $controller;

    protected function setUp(): void
    {
        $this->addSectionHandler = $this->createMock(AddSiteSectionHandler::class);
        $this->addSectionItemHandler = $this->createMock(AddSiteSectionItemHandler::class);
        $this->listSectionsHandler = $this->createMock(ListSiteSectionsHandler::class);
        $this->listSectionItemsHandler = $this->createMock(ListSiteSectionItemsHandler::class);
        $this->updateSectionHandler = $this->createMock(UpdateSiteSectionHandler::class);
        $this->updateSectionItemHandler = $this->createMock(UpdateSiteSectionItemHandler::class);
        $this->deleteSectionHandler = $this->createMock(DeleteSiteSectionHandler::class);
        $this->deleteSectionItemHandler = $this->createMock(DeleteSiteSectionItemHandler::class);

        $this->controller = new SiteController(
            $this->createMock(CreateSiteHandler::class),
            $this->createMock(ListSitesHandler::class),
            $this->createMock(GetSiteHandler::class),
            $this->createMock(AssignUserToSiteHandler::class),
            $this->createMock(UnassignUserFromSiteHandler::class),
            $this->createMock(UpdateSiteHandler::class),
            $this->createMock(DeleteSiteHandler::class),
            new SiteSectionActionHandlers(
                $this->listSectionsHandler,
                $this->addSectionHandler,
                $this->createMock(ReorderSiteSectionsHandler::class),
                $this->updateSectionHandler,
                $this->deleteSectionHandler,
                $this->listSectionItemsHandler,
                $this->addSectionItemHandler,
                $this->updateSectionItemHandler,
                $this->deleteSectionItemHandler,
            ),
        );
    }

    public function testListSectionsReturns200AndSerializedBody(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/admin/sites/site-1/sections")
            ->withAttribute("id", "site-1");
        $response = (new ResponseFactory())->createResponse();

        $this->listSectionsHandler->expects($this->once())
            ->method("handle")
            ->willReturn([["id" => "sec-1", "type" => "text", "title" => "Intro", "data" => [], "position" => 0]]);

        $result = $this->controller->listSections($request, $response);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertStringContainsString('"id":"sec-1"', (string)$result->getBody());
    }

    public function testListSectionsReturns400WhenHandlerThrows(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/admin/sites/site-1/sections")
            ->withAttribute("id", "site-1");
        $response = (new ResponseFactory())->createResponse();

        $this->listSectionsHandler->expects($this->once())
            ->method("handle")
            ->willThrowException(new \InvalidArgumentException("bad sections"));

        $result = $this->controller->listSections($request, $response);

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString("bad sections", (string)$result->getBody());
    }

    public function testListSectionsUsesRouteArgumentFallback(): void
    {
        $route = $this->createMock(RouteInterface::class);
        $route->expects($this->once())->method("getArgument")->with("id")->willReturn("site-from-route");

        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/admin/sites/site-from-route/sections")
            ->withAttribute(RouteContext::ROUTE, $route)
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->createMock(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, new RoutingResults(
                $this->createMock(DispatcherInterface::class),
                "GET",
                "/admin/sites/site-from-route/sections",
                RoutingResults::FOUND,
            ))
            ->withAttribute("id", "");

        $this->listSectionsHandler->expects($this->once())->method("handle");

        $result = $this->controller->listSections($request, (new ResponseFactory())->createResponse());
        $this->assertSame(200, $result->getStatusCode());
    }

    public function testListSectionsTrimsRouteSiteIdFallback(): void
    {
        $route = $this->createMock(RouteInterface::class);
        $route->expects($this->once())->method("getArgument")->with("id")->willReturn("  site-from-route  ");

        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/admin/sites/site-from-route/sections")
            ->withAttribute(RouteContext::ROUTE, $route)
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->createMock(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, new RoutingResults(
                $this->createMock(DispatcherInterface::class),
                "GET",
                "/admin/sites/site-from-route/sections",
                RoutingResults::FOUND,
            ))
            ->withAttribute("id", "");

        $this->listSectionsHandler->expects($this->once())
            ->method("handle")
            ->with($this->callback(static fn($query): bool => $query->siteId === "site-from-route"))
            ->willReturn([]);

        $result = $this->controller->listSections($request, (new ResponseFactory())->createResponse());
        $this->assertSame(200, $result->getStatusCode());
    }

    public function testListSectionsReturns400WhenSiteIdCannotBeResolved(): void
    {
        $route = $this->createMock(RouteInterface::class);
        $route->expects($this->once())->method("getArgument")->with("id")->willReturn(null);

        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/admin/sites//sections")
            ->withAttribute(RouteContext::ROUTE, $route)
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->createMock(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, new RoutingResults(
                $this->createMock(DispatcherInterface::class),
                "GET",
                "/admin/sites//sections",
                RoutingResults::FOUND,
            ));

        $result = $this->controller->listSections($request, (new ResponseFactory())->createResponse());

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString("Site ID is required", (string)$result->getBody());
    }

    public function testListSectionsHandlesNullRouteWithoutFatalError(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/admin/sites//sections")
            ->withAttribute(RouteContext::ROUTE, null)
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->createMock(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, new RoutingResults(
                $this->createMock(DispatcherInterface::class),
                "GET",
                "/admin/sites//sections",
                RoutingResults::FOUND,
            ))
            ->withAttribute("id", "");

        $result = $this->controller->listSections($request, (new ResponseFactory())->createResponse());

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString("Site ID is required", (string)$result->getBody());
    }

    public function testCreateSectionReturns201WithPayload(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/site-1/sections")
            ->withAttribute("id", "site-1")
            ->withParsedBody(["type" => "text", "title" => "Hero"]);
        $response = (new ResponseFactory())->createResponse();

        $this->addSectionHandler->expects($this->once())
            ->method("handle")
            ->willReturn(["id" => "sec-1", "type" => "text", "title" => "Hero", "data" => [], "position" => 0]);

        $result = $this->controller->createSection($request, $response);

        $this->assertSame(201, $result->getStatusCode());
        $this->assertStringContainsString('"id":"sec-1"', (string)$result->getBody());
    }

    public function testCreateSectionReturns400WhenHandlerThrows(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/site-1/sections")
            ->withAttribute("id", "site-1")
            ->withParsedBody(["type" => "text", "title" => "Hero"]);
        $response = (new ResponseFactory())->createResponse();

        $this->addSectionHandler->expects($this->once())
            ->method("handle")
            ->willThrowException(new \InvalidArgumentException("bad section"));

        $result = $this->controller->createSection($request, $response);

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString("bad section", (string)$result->getBody());
    }

    public function testUpdateSectionReturns200WithPayload(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/site-1/sections/sec-1")
            ->withAttribute("id", "site-1")
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["title" => "Hero Updated", "data" => ["value" => "x"]]);
        $response = (new ResponseFactory())->createResponse();

        $this->updateSectionHandler->expects($this->once())
            ->method("handle")
            ->willReturn(["id" => "sec-1", "type" => "text", "title" => "Hero Updated", "data" => ["value" => "x"], "position" => 0]);

        $result = $this->controller->updateSection($request, $response);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertStringContainsString("Hero Updated", (string)$result->getBody());
    }

    public function testUpdateSectionReturns400WhenHandlerThrows(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/site-1/sections/sec-1")
            ->withAttribute("id", "site-1")
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["title" => "Hero Updated", "data" => ["value" => "x"]]);

        $this->updateSectionHandler->expects($this->once())
            ->method("handle")
            ->willThrowException(new \InvalidArgumentException("bad update"));

        $result = $this->controller->updateSection($request, (new ResponseFactory())->createResponse());

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString("bad update", (string)$result->getBody());
    }

    public function testDeleteSectionValidatesSiteAndSectionIds(): void
    {
        $response = (new ResponseFactory())->createResponse();

        $missingSite = (new ServerRequestFactory())
            ->createServerRequest("DELETE", "/admin/sites//sections/sec-1")
            ->withAttribute("sectionId", "sec-1");
        $missingSection = (new ServerRequestFactory())
            ->createServerRequest("DELETE", "/admin/sites/site-1/sections/")
            ->withAttribute("id", "site-1");

        $siteResult = $this->controller->deleteSection($missingSite, $response);
        $sectionResult = $this->controller->deleteSection($missingSection, (new ResponseFactory())->createResponse());

        $this->assertSame(400, $siteResult->getStatusCode());
        $this->assertStringContainsString("Site ID is required", (string)$siteResult->getBody());
        $this->assertSame(400, $sectionResult->getStatusCode());
        $this->assertStringContainsString("Section ID is required", (string)$sectionResult->getBody());
    }

    public function testDeleteSectionReturns204OnSuccess(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("DELETE", "/admin/sites/site-1/sections/sec-1")
            ->withAttribute("id", "site-1")
            ->withAttribute("sectionId", "sec-1");

        $this->deleteSectionHandler->expects($this->once())->method("handle");

        $result = $this->controller->deleteSection($request, (new ResponseFactory())->createResponse());

        $this->assertSame(204, $result->getStatusCode());
    }

    public function testListSectionItemsReturns200(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("GET", "/admin/sites/site-1/sections/sec-1/items")
            ->withAttribute("id", "site-1")
            ->withAttribute("sectionId", "sec-1");

        $this->listSectionItemsHandler->expects($this->once())
            ->method("handle")
            ->willReturn([["id" => "item-1", "title" => "Card"]]);

        $result = $this->controller->listSectionItems($request, (new ResponseFactory())->createResponse());

        $this->assertSame(200, $result->getStatusCode());
        $this->assertStringContainsString("item-1", (string)$result->getBody());
    }

    public function testListSectionItemsValidatesSiteAndSectionIds(): void
    {
        $missingSite = (new ServerRequestFactory())
            ->createServerRequest("GET", "/admin/sites//sections/sec-1/items")
            ->withAttribute("sectionId", "sec-1");
        $missingSection = (new ServerRequestFactory())
            ->createServerRequest("GET", "/admin/sites/site-1/sections//items")
            ->withAttribute("id", "site-1");

        $siteResult = $this->controller->listSectionItems($missingSite, (new ResponseFactory())->createResponse());
        $sectionResult = $this->controller->listSectionItems($missingSection, (new ResponseFactory())->createResponse());

        $this->assertSame(400, $siteResult->getStatusCode());
        $this->assertStringContainsString("Site ID is required", (string)$siteResult->getBody());
        $this->assertSame(400, $sectionResult->getStatusCode());
        $this->assertStringContainsString("Section ID is required", (string)$sectionResult->getBody());
    }

    public function testCreateAndUpdateSectionItemEndpoints(): void
    {
        $createRequest = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/site-1/sections/sec-1/items")
            ->withAttribute("id", "site-1")
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["data" => ["title" => "Card"]]);

        $this->addSectionItemHandler->expects($this->once())
            ->method("handle")
            ->willReturn(["id" => "item-1", "title" => "Card"]);

        $createResult = $this->controller->createSectionItem($createRequest, (new ResponseFactory())->createResponse());

        $this->assertSame(201, $createResult->getStatusCode());
        $this->assertStringContainsString("item-1", (string)$createResult->getBody());

        $updateRequest = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/site-1/sections/sec-1/items/item-1")
            ->withAttribute("id", "site-1")
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody(["data" => ["title" => "Card Updated"]]);

        $this->updateSectionItemHandler->expects($this->once())
            ->method("handle")
            ->willReturn(["id" => "item-1", "title" => "Card Updated"]);

        $updateResult = $this->controller->updateSectionItem($updateRequest, (new ResponseFactory())->createResponse());

        $this->assertSame(200, $updateResult->getStatusCode());
        $this->assertStringContainsString("Card Updated", (string)$updateResult->getBody());
    }

    public function testCreateSectionItemReturns400OnInvalidPayload(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("POST", "/admin/sites/site-1/sections/sec-1/items")
            ->withAttribute("id", "site-1")
            ->withAttribute("sectionId", "sec-1")
            ->withParsedBody(["data" => "invalid"]);

        $result = $this->controller->createSectionItem($request, (new ResponseFactory())->createResponse());

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString("Item data is required", (string)$result->getBody());
    }

    public function testUpdateSectionItemReturns400OnInvalidPayload(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/site-1/sections/sec-1/items/item-1")
            ->withAttribute("id", "site-1")
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1")
            ->withParsedBody(["data" => "invalid"]);

        $result = $this->controller->updateSectionItem($request, (new ResponseFactory())->createResponse());

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString("Item data is required", (string)$result->getBody());
    }

    public function testDeleteSectionItemEndpointValidatesInputsAndReturns204(): void
    {
        $missingItem = (new ServerRequestFactory())
            ->createServerRequest("DELETE", "/admin/sites/site-1/sections/sec-1/items/")
            ->withAttribute("id", "site-1")
            ->withAttribute("sectionId", "sec-1");

        $invalidResult = $this->controller->deleteSectionItem($missingItem, (new ResponseFactory())->createResponse());
        $this->assertSame(400, $invalidResult->getStatusCode());
        $this->assertStringContainsString("Item ID is required", (string)$invalidResult->getBody());

        $request = (new ServerRequestFactory())
            ->createServerRequest("DELETE", "/admin/sites/site-1/sections/sec-1/items/item-1")
            ->withAttribute("id", "site-1")
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1");

        $this->deleteSectionItemHandler->expects($this->once())->method("handle");

        $result = $this->controller->deleteSectionItem($request, (new ResponseFactory())->createResponse());
        $this->assertSame(204, $result->getStatusCode());

        $missingSite = (new ServerRequestFactory())
            ->createServerRequest("DELETE", "/admin/sites//sections/sec-1/items/item-1")
            ->withAttribute("sectionId", "sec-1")
            ->withAttribute("itemId", "item-1");
        $missingSection = (new ServerRequestFactory())
            ->createServerRequest("DELETE", "/admin/sites/site-1/sections//items/item-1")
            ->withAttribute("id", "site-1")
            ->withAttribute("itemId", "item-1");

        $missingSiteResult = $this->controller->deleteSectionItem($missingSite, (new ResponseFactory())->createResponse());
        $missingSectionResult = $this->controller->deleteSectionItem($missingSection, (new ResponseFactory())->createResponse());

        $this->assertSame(400, $missingSiteResult->getStatusCode());
        $this->assertStringContainsString("Site ID is required", (string)$missingSiteResult->getBody());
        $this->assertSame(400, $missingSectionResult->getStatusCode());
        $this->assertStringContainsString("Section ID is required", (string)$missingSectionResult->getBody());
    }

    public function testReorderSectionsReturns400OnInvalidPayload(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest("PUT", "/admin/sites/site-1/sections/order")
            ->withAttribute("id", "site-1")
            ->withParsedBody(["sectionIds" => "invalid"]);

        $result = $this->controller->reorderSections($request, (new ResponseFactory())->createResponse());

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString("sectionIds must be an array", (string)$result->getBody());
    }
}
