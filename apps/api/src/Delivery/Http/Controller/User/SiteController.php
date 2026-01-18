<?php

declare(strict_types=1);

namespace App\Delivery\Http\Controller\User;

use App\Application\Site\Handler\GetUserSitesHandler;
use App\Application\Site\Query\GetUserSitesQuery;
use App\Delivery\Http\Resource\SiteResource;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SiteController
{
    public function __construct(
        private GetUserSitesHandler $getUserSitesHandler,
    ) {
    }

    public function listAssigned(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $request->getAttribute('userId'); // Assuming JwtAuthMiddleware puts userId in attribute

        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'User ID not found in request'], JSON_THROW_ON_ERROR));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $query = new GetUserSitesQuery($userId);
        $sites = $this->getUserSitesHandler->handle($query);
        $data = SiteResource::collectionToArray($sites);

        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
