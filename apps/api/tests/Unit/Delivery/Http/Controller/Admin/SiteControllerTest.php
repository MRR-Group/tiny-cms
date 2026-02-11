<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Controller\Admin;

use App\Application\Site\Command\AssignUserToSiteCommand;
use App\Application\Site\Command\CreateSiteCommand;
use App\Application\Site\Command\ReorderSiteSectionsCommand;
use App\Application\Site\Command\UnassignUserFromSiteCommand;
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
use App\Application\Site\Query\GetSiteQuery;
use App\Application\Site\Query\ListSiteSectionsQuery;
use App\Application\Site\Query\ListSitesQuery;
use App\Delivery\Http\Controller\Admin\SiteController;
use App\Delivery\Http\Controller\Admin\SiteSectionActionHandlers;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class SiteControllerTest extends TestCase
{
    private CreateSiteHandler&MockObject $createHandler;
    private AssignUserToSiteHandler&MockObject $assignHandler;
    private ListSitesHandler&MockObject $listHandler;
    private GetSiteHandler&MockObject $getHandler;
    private UnassignUserFromSiteHandler&MockObject $unassignHandler;
    private UpdateSiteHandler&MockObject $updateHandler;
    private DeleteSiteHandler&MockObject $deleteHandler;
    private ListSiteSectionsHandler&MockObject $listSectionsHandler;
    private AddSiteSectionHandler&MockObject $addSectionHandler;
    private AddSiteSectionItemHandler&MockObject $addSectionItemHandler;
    private ReorderSiteSectionsHandler&MockObject $reorderSectionsHandler;
    private UpdateSiteSectionHandler&MockObject $updateSectionHandler;
    private DeleteSiteSectionHandler&MockObject $deleteSectionHandler;
    private ListSiteSectionItemsHandler&MockObject $listSectionItemsHandler;
    private UpdateSiteSectionItemHandler&MockObject $updateSectionItemHandler;
    private DeleteSiteSectionItemHandler&MockObject $deleteSectionItemHandler;
    private SiteController $controller;

    protected function setUp(): void
    {
        $this->createHandler = $this->createMock(CreateSiteHandler::class);
        $this->listHandler = $this->createMock(ListSitesHandler::class);
        $this->getHandler = $this->createMock(GetSiteHandler::class);
        $this->assignHandler = $this->createMock(AssignUserToSiteHandler::class);
        $this->unassignHandler = $this->createMock(UnassignUserFromSiteHandler::class);
        $this->updateHandler = $this->createMock(UpdateSiteHandler::class);
        $this->deleteHandler = $this->createMock(DeleteSiteHandler::class);
        $this->listSectionsHandler = $this->createMock(ListSiteSectionsHandler::class);
        $this->addSectionHandler = $this->createMock(AddSiteSectionHandler::class);
        $this->addSectionItemHandler = $this->createMock(AddSiteSectionItemHandler::class);
        $this->reorderSectionsHandler = $this->createMock(ReorderSiteSectionsHandler::class);
        $this->updateSectionHandler = $this->createMock(UpdateSiteSectionHandler::class);
        $this->deleteSectionHandler = $this->createMock(DeleteSiteSectionHandler::class);
        $this->listSectionItemsHandler = $this->createMock(ListSiteSectionItemsHandler::class);
        $this->updateSectionItemHandler = $this->createMock(UpdateSiteSectionItemHandler::class);
        $this->deleteSectionItemHandler = $this->createMock(DeleteSiteSectionItemHandler::class);

        $this->controller = new SiteController(
            $this->createHandler,
            $this->listHandler,
            $this->getHandler,
            $this->assignHandler,
            $this->unassignHandler,
            $this->updateHandler,
            $this->deleteHandler,
            new SiteSectionActionHandlers(
                $this->listSectionsHandler,
                $this->addSectionHandler,
                $this->reorderSectionsHandler,
                $this->updateSectionHandler,
                $this->deleteSectionHandler,
                $this->listSectionItemsHandler,
                $this->addSectionItemHandler,
                $this->updateSectionItemHandler,
                $this->deleteSectionItemHandler,
            ),
        );
    }

    public function testListSectionsReturns200(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/admin/sites/{$siteId}/sections")
            ->withAttribute("id", $siteId);
        $response = (new ResponseFactory())->createResponse();

        $this->listSectionsHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(ListSiteSectionsQuery::class))
            ->willReturn([
                ["id" => "sec-1", "type" => "text", "title" => "Intro", "position" => 0],
            ]);

        $result = $this->controller->listSections($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testReorderSectionsReturns204(): void
    {
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/admin/sites/{$siteId}/sections/order")
            ->withAttribute("id", $siteId)
            ->withParsedBody(["sectionIds" => ["sec-2", "sec-1"]]);
        $response = (new ResponseFactory())->createResponse();

        $this->reorderSectionsHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(ReorderSiteSectionsCommand::class));

        $result = $this->controller->reorderSections($request, $response);

        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testCreateSiteReturns201(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/sites")
            ->withParsedBody([
                "name" => "My Site",
                "url" => "http://example.com",
                "type" => "dynamic",
            ]);
        $response = (new ResponseFactory())->createResponse();

        $this->createHandler->expects($this->once())
            ->method("handle")
            ->with($this->callback(fn(CreateSiteCommand $c) => $c->name === "My Site"))
            ->willReturn(SiteId::generate());

        $result = $this->controller->create($request, $response, []);

        $this->assertEquals(201, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey("id", $body);
        $this->assertNotEmpty($body["id"]);
    }

    public function testCreateSiteHandlesErrors(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/sites")
            ->withParsedBody([
                "name" => "My Site",
                "url" => "http://example.com",
                "type" => "invalid",
            ]);
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->create($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey("error", $body);
        $this->assertNotEmpty($body["error"]);
    }

    public function testListSitesReturns200AndData(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/admin/sites");
        $response = (new ResponseFactory())->createResponse();

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn(SiteId::generate());
        $site->method("getName")->willReturn("Site 1");
        $site->method("getUrl")->willReturn("url");
        $site->method("getType")->willReturn(SiteType::STATIC);
        $site->method("getCreatedAt")->willReturn(new \DateTimeImmutable());

        $this->listHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(ListSitesQuery::class))
            ->willReturn([$site]);

        $result = $this->controller->list($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertCount(1, $body);
    }

    public function testAssignUserReturns204(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/sites/assign")
            ->withParsedBody(["userId" => "uid", "siteId" => "sid"]);
        $response = (new ResponseFactory())->createResponse();

        $this->assignHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(AssignUserToSiteCommand::class));

        $result = $this->controller->assignUser($request, $response, []);

        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testAssignUserHandlesErrors(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest("POST", "/admin/sites/assign")
            ->withParsedBody(["userId" => "uid", "siteId" => "sid"]);
        $response = (new ResponseFactory())->createResponse();

        $this->assignHandler->expects($this->once())
            ->method("handle")
            ->willThrowException(new \InvalidArgumentException("Error message"));

        $result = $this->controller->assignUser($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertEquals("Error message", $body["error"]);
    }

    public function testUpdateSiteReturns200(): void
    {
        $id = SiteId::generate()->toString();
        $data = [
            "name" => "Updated Site",
            "url" => "http://updated.com",
            "type" => "static",
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/admin/sites/$id")
            ->withAttribute("id", $id);
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $response = (new ResponseFactory())->createResponse();

        $this->updateHandler->expects($this->once())
            ->method("handle");

        $result = $this->controller->update($request, $response, []);

        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testDeleteSiteReturns204(): void
    {
        $id = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())->createServerRequest("DELETE", "/admin/sites/$id")
            ->withAttribute("id", $id);
        $response = (new ResponseFactory())->createResponse();

        $this->deleteHandler->expects($this->once())
            ->method("handle");

        $result = $this->controller->delete($request, $response, []);

        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testDeleteSiteValidationErrors(): void
    {
        // Array ID
        $request = (new ServerRequestFactory())->createServerRequest("DELETE", "/admin/sites/123")
            ->withAttribute("id", ["id"]);
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->delete($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertEquals("Site ID is required", $body["error"]);

        // Empty string ID
        $request = (new ServerRequestFactory())->createServerRequest("DELETE", "/admin/sites/")
            ->withAttribute("id", "");
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->delete($request, $response, []);
        $this->assertEquals(400, $result->getStatusCode());
    }

    public function testUpdateSiteHandlesErrors(): void
    {
        $id = SiteId::generate()->toString();
        $data = [
            "name" => "Updated Site",
            "url" => "http://updated.com",
            "type" => "static",
        ];
        $request = (new ServerRequestFactory())->createServerRequest("PUT", "/admin/sites/$id")
            ->withAttribute("id", $id);
        $request->getBody()->write(json_encode($data));
        $request->getBody()->rewind();

        $response = (new ResponseFactory())->createResponse();

        $this->updateHandler->expects($this->once())
            ->method("handle")
            ->willThrowException(new \InvalidArgumentException("Update error"));

        $result = $this->controller->update($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey("error", $body);
        $this->assertEquals("Update error", $body["error"]);
    }

    public function testGetSiteReturns200(): void
    {
        $id = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/admin/sites/$id")
            ->withAttribute("id", $id);
        $response = (new ResponseFactory())->createResponse();

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn(SiteId::fromString($id));
        $site->method("getName")->willReturn("Site 1");
        $site->method("getUrl")->willReturn("url");
        $site->method("getType")->willReturn(SiteType::STATIC);
        $site->method("getCreatedAt")->willReturn(new \DateTimeImmutable());
        $site->method("getEditorCount")->willReturn(1);

        $user = $this->createMock(User::class);
        $userId = "00000000-0000-0000-0000-000000000001";
        $user->method("getId")->willReturn(UserId::fromString($userId));
        $user->method("getEmail")->willReturn(new Email("test@example.com"));
        $user->method("getRole")->willReturn(Role::editor());

        $collection = $this->createMock(Collection::class);
        $collection->method("toArray")->willReturn([$user]);
        $site->method("getUsers")->willReturn($collection);

        $this->getHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(GetSiteQuery::class))
            ->willReturn($site);

        $result = $this->controller->get($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey("id", $body);
        $this->assertEquals($id, $body["id"]);
        $this->assertArrayHasKey("editors", $body);
        $this->assertCount(1, $body["editors"]);
        $this->assertArrayHasKey("id", $body["editors"][0]);
        $this->assertEquals("00000000-0000-0000-0000-000000000001", $body["editors"][0]["id"]);
        $this->assertEquals("test@example.com", $body["editors"][0]["email"]);
        $this->assertEquals("editor", $body["editors"][0]["role"]);
    }

    public function testGetSiteValidationErrors(): void
    {
        // Missing/Invalid ID
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/admin/sites/")
            ->withAttribute("id", []); // Invalid
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->get($request, $response, []);

        // Controller returns 404 for exceptions in get() (including validation)
        $this->assertEquals(404, $result->getStatusCode()); 
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey("error", $body);
        $this->assertEquals("Site ID is required", $body["error"]);

        // Empty string ID (kills LogicalOr on !is_string || empty)
        $request = (new ServerRequestFactory())->createServerRequest("GET", "/admin/sites/")
            ->withAttribute("id", ""); // Empty string
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->get($request, $response, []);
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testUnassignUserReturns204(): void
    {
        $siteId = SiteId::generate()->toString();
        $userId = "uid";

        $request = (new ServerRequestFactory())->createServerRequest("DELETE", "/admin/sites/$siteId/users/$userId")
            ->withAttribute("id", $siteId)
            ->withAttribute("userId", $userId);
        $response = (new ResponseFactory())->createResponse();

        $this->unassignHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(UnassignUserFromSiteCommand::class));

        $result = $this->controller->unassignUser($request, $response, []);

        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testUnassignUserValidationErrors(): void
    {
        // Missing userId
        $siteId = SiteId::generate()->toString();
        $request = (new ServerRequestFactory())->createServerRequest("DELETE", "/admin/sites/$siteId/users/")
            ->withAttribute("id", $siteId);
        // userId missing
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->unassignUser($request, $response, []);
        $this->assertEquals(400, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey("error", $body);
        $this->assertEquals("Site ID and User ID are required", $body["error"]);

        // Invalid siteId (array)
        $request = (new ServerRequestFactory())->createServerRequest("DELETE", "/admin/sites//users/uid")
            ->withAttribute("userId", "uid")
            ->withAttribute("id", []); // array
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->unassignUser($request, $response, []);
        $this->assertEquals(400, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey("error", $body);
        $this->assertEquals("Site ID and User ID are required", $body["error"]);

        // Empty string siteId
        $request = (new ServerRequestFactory())->createServerRequest("DELETE", "/admin/sites//users/uid")
            ->withAttribute("userId", "uid")
            ->withAttribute("id", ""); 
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->unassignUser($request, $response, []);
        $this->assertEquals(400, $result->getStatusCode());
    }
}
