<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

class SiteAccessMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userId = $request->getAttribute("userId");
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

        if (!is_string($userId) || $userId === "") {
            return $this->jsonError(401, "Unauthorized");
        }

        if (!is_string($siteId) || $siteId === "") {
            return $this->jsonError(400, "Site ID is required");
        }

        $site = $this->siteRepository->findById(SiteId::fromString($siteId));

        if ($site === null) {
            return $this->jsonError(404, "Site not found");
        }

        if (!$site->hasAssignedUser($userId)) {
            return $this->jsonError(403, "Forbidden");
        }

        return $handler->handle($request);
    }

    private function jsonError(int $status, string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode(["error" => $message], JSON_THROW_ON_ERROR));

        return $response->withStatus($status)->withHeader("Content-Type", "application/json");
    }
}
