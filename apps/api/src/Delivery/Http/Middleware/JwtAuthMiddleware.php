<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Application\Auth\Contract\TokenValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TokenValidatorInterface $tokenValidator,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine("Authorization");

        if (!preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $response = new Response();
            $response->getBody()->write((string)json_encode(["error" => "Unauthorized"]));

            return $response->withStatus(401)->withHeader("Content-Type", "application/json");
        }

        $token = $matches[1];
        $claims = $this->tokenValidator->validate($token);

        if (!$claims) {
            $response = new Response();
            $response->getBody()->write((string)json_encode(["error" => "Invalid token"]));

            return $response->withStatus(401)->withHeader("Content-Type", "application/json");
        }

        $request = $request->withAttribute("user_id", $claims["sub"])
            ->withAttribute("role", $claims["role"]);

        return $handler->handle($request);
    }
}
