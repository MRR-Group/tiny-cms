<?php

declare(strict_types=1);

namespace TinyCms\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;

final class Application
{
    public static function create(): \Slim\App
    {
        $app = AppFactory::create();

        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();
        $app->addErrorMiddleware(true, true, true);

        $app->get('/health', static function (ServerRequestInterface $request, Response $response): ResponseInterface {
            $payload = [
                'status' => 'ok',
            ];

            $response->getBody()->write((string) json_encode($payload, JSON_THROW_ON_ERROR));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        });

        return $app;
    }
}
