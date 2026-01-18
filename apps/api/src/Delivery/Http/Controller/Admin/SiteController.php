<?php

declare(strict_types=1);

namespace App\Delivery\Http\Controller\Admin;

use App\Application\Site\Handler\AssignUserToSiteHandler;
use App\Application\Site\Handler\CreateSiteHandler;
use App\Application\Site\Handler\ListSitesHandler;
use App\Application\Site\Query\ListSitesQuery;
use App\Delivery\Http\Request\Site\AssignUserToSiteRequest;
use App\Delivery\Http\Request\Site\CreateSiteRequest;
use App\Delivery\Http\Resource\SiteResource;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SiteController
{
    public function __construct(
        private CreateSiteHandler $createHandler,
        private ListSitesHandler $listHandler,
        private AssignUserToSiteHandler $assignHandler,
    ) {
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = CreateSiteRequest::fromPsr7($request);
            $siteId = $this->createHandler->handle($command);

            $response->getBody()->write(json_encode(['id' => (string) $siteId], JSON_THROW_ON_ERROR));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $sites = $this->listHandler->handle(new ListSitesQuery());
        $data = SiteResource::collectionToArray($sites);

        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function assignUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $command = AssignUserToSiteRequest::fromPsr7($request);
            $this->assignHandler->handle($command);

            return $response->withHeader('Content-Type', 'application/json')->withStatus(204);
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
