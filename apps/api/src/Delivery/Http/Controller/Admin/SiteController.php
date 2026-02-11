<?php

declare(strict_types=1);

namespace App\Delivery\Http\Controller\Admin;

use App\Application\Site\Command\DeleteSiteCommand;
use App\Application\Site\Command\DeleteSiteSectionCommand;
use App\Application\Site\Command\DeleteSiteSectionItemCommand;
use App\Application\Site\Command\UnassignUserFromSiteCommand;
use App\Application\Site\Handler\AssignUserToSiteHandler;
use App\Application\Site\Handler\CreateSiteHandler;
use App\Application\Site\Handler\DeleteSiteHandler;
use App\Application\Site\Handler\GetSiteHandler;
use App\Application\Site\Handler\ListSitesHandler;
use App\Application\Site\Handler\UnassignUserFromSiteHandler;
use App\Application\Site\Handler\UpdateSiteHandler;
use App\Application\Site\Query\GetSiteQuery;
use App\Application\Site\Query\ListSiteSectionItemsQuery;
use App\Application\Site\Query\ListSiteSectionsQuery;
use App\Application\Site\Query\ListSitesQuery;
use App\Delivery\Http\Request\Site\AssignUserToSiteRequest;
use App\Delivery\Http\Request\Site\CreateSiteRequest;
use App\Delivery\Http\Request\Site\CreateSiteSectionItemRequest;
use App\Delivery\Http\Request\Site\CreateSiteSectionRequest;
use App\Delivery\Http\Request\Site\ReorderSiteSectionsRequest;
use App\Delivery\Http\Request\Site\UpdateSiteRequest;
use App\Delivery\Http\Request\Site\UpdateSiteSectionItemRequest;
use App\Delivery\Http\Request\Site\UpdateSiteSectionRequest;
use App\Delivery\Http\Resource\SiteResource;
use App\Delivery\Http\Resource\SiteSectionResource;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;

class SiteController
{
    public function __construct(
        private CreateSiteHandler $createHandler,
        private ListSitesHandler $listHandler,
        private GetSiteHandler $getHandler,
        private AssignUserToSiteHandler $assignHandler,
        private UnassignUserFromSiteHandler $unassignHandler,
        private UpdateSiteHandler $updateHandler,
        private DeleteSiteHandler $deleteHandler,
        private SiteSectionActionHandlers $sectionHandlers,
    ) {}

    public function listSections(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $siteId = $request->getAttribute("id");

            if (!is_string($siteId) || $siteId === "") {
                $route = RouteContext::fromRequest($request)->getRoute();
                $routeSiteId = $route?->getArgument("id");

                if (is_string($routeSiteId)) {
                    $normalizedRouteSiteId = trim($routeSiteId);

                    if ($normalizedRouteSiteId !== "") {
                        $siteId = $normalizedRouteSiteId;
                    }
                }
            }

            if (!is_string($siteId) || $siteId === "") {
                throw new \InvalidArgumentException("Site ID is required");
            }

            $sections = $this->sectionHandlers->listSectionsHandler->handle(new ListSiteSectionsQuery($siteId));
            $response->getBody()->write(json_encode(SiteSectionResource::collectionToArray($sections), JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(200);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function createSection(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = CreateSiteSectionRequest::fromPsr7($request);
            $section = $this->sectionHandlers->addSectionHandler->handle($command);

            $response->getBody()->write(json_encode(SiteSectionResource::toArray($section), JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(201);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function reorderSections(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = ReorderSiteSectionsRequest::fromPsr7($request);
            $this->sectionHandlers->reorderSectionsHandler->handle($command);

            return $response->withHeader("Content-Type", "application/json")->withStatus(204);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function updateSection(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = UpdateSiteSectionRequest::fromPsr7($request);
            $section = $this->sectionHandlers->updateSectionHandler->handle($command);

            $response->getBody()->write(json_encode(SiteSectionResource::toArray($section), JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(200);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function deleteSection(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $siteId = $request->getAttribute("id");
            $sectionId = $request->getAttribute("sectionId");

            if (!is_string($siteId) || $siteId === "") {
                throw new \InvalidArgumentException("Site ID is required");
            }

            if (!is_string($sectionId) || $sectionId === "") {
                throw new \InvalidArgumentException("Section ID is required");
            }

            $this->sectionHandlers->deleteSectionHandler->handle(new DeleteSiteSectionCommand($siteId, $sectionId));

            return $response->withHeader("Content-Type", "application/json")->withStatus(204);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function listSectionItems(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $siteId = $request->getAttribute("id");
            $sectionId = $request->getAttribute("sectionId");

            if (!is_string($siteId) || $siteId === "") {
                throw new \InvalidArgumentException("Site ID is required");
            }

            if (!is_string($sectionId) || $sectionId === "") {
                throw new \InvalidArgumentException("Section ID is required");
            }

            $items = $this->sectionHandlers->listSectionItemsHandler->handle(new ListSiteSectionItemsQuery($siteId, $sectionId));
            $response->getBody()->write(json_encode($items, JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(200);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function createSectionItem(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = CreateSiteSectionItemRequest::fromPsr7($request);
            $item = $this->sectionHandlers->addSectionItemHandler->handle($command);
            $response->getBody()->write(json_encode($item, JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(201);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function updateSectionItem(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = UpdateSiteSectionItemRequest::fromPsr7($request);
            $item = $this->sectionHandlers->updateSectionItemHandler->handle($command);
            $response->getBody()->write(json_encode($item, JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(200);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function deleteSectionItem(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $siteId = $request->getAttribute("id");
            $sectionId = $request->getAttribute("sectionId");
            $itemId = $request->getAttribute("itemId");

            if (!is_string($siteId) || $siteId === "") {
                throw new \InvalidArgumentException("Site ID is required");
            }

            if (!is_string($sectionId) || $sectionId === "") {
                throw new \InvalidArgumentException("Section ID is required");
            }

            if (!is_string($itemId) || $itemId === "") {
                throw new \InvalidArgumentException("Item ID is required");
            }

            $this->sectionHandlers->deleteSectionItemHandler->handle(new DeleteSiteSectionItemCommand($siteId, $sectionId, $itemId));

            return $response->withHeader("Content-Type", "application/json")->withStatus(204);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function unassignUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $siteId = $request->getAttribute("id");
            $userId = $request->getAttribute("userId");

            if (!is_string($siteId) || empty($siteId) || !is_string($userId) || empty($userId)) {
                throw new \InvalidArgumentException("Site ID and User ID are required");
            }

            $command = new UnassignUserFromSiteCommand($userId, $siteId);
            $this->unassignHandler->handle($command);

            return $response->withHeader("Content-Type", "application/json")->withStatus(204);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function get(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $siteId = $request->getAttribute("id");

            if (!is_string($siteId) || empty($siteId)) {
                throw new \InvalidArgumentException("Site ID is required");
            }

            $site = $this->getHandler->handle(new GetSiteQuery($siteId));
            $data = SiteResource::toDetailArray($site);

            $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(200);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(404);
        }
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = CreateSiteRequest::fromPsr7($request);
            $siteId = $this->createHandler->handle($command);

            $response->getBody()->write(json_encode(["id" => (string)$siteId], JSON_THROW_ON_ERROR));

            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(201);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $sites = $this->listHandler->handle(new ListSitesQuery());
        $data = SiteResource::collectionToArray($sites);

        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response->withHeader("Content-Type", "application/json")->withStatus(200);
    }

    public function assignUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = AssignUserToSiteRequest::fromPsr7($request);
            $this->assignHandler->handle($command);

            return $response->withHeader("Content-Type", "application/json")->withStatus(204);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = UpdateSiteRequest::fromPsr7($request);
            $this->updateHandler->handle($command);

            return $response->withHeader("Content-Type", "application/json")->withStatus(204);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $siteId = $request->getAttribute("id");

            if (!is_string($siteId) || empty($siteId)) {
                throw new \InvalidArgumentException("Site ID is required");
            }

            $command = new DeleteSiteCommand($siteId);
            $this->deleteHandler->handle($command);

            return $response->withHeader("Content-Type", "application/json")->withStatus(204);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()], JSON_THROW_ON_ERROR));

            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    }
}
