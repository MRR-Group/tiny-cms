<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $requiredRole,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $role = $request->getAttribute("role");

        if ($role !== $this->requiredRole) {
            $response = new Response();
            $response->getBody()->write((string)json_encode(["error" => "Forbidden"]));

            return $response->withStatus(403)->withHeader("Content-Type", "application/json");
        }

        return $handler->handle($request);
    }
}
