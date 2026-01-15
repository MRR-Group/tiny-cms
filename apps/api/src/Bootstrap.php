<?php

declare(strict_types=1);

namespace TinyCMS\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;

final class Bootstrap
{
    public static function createApp(): App
    {
        $app = AppFactory::create();

        $app->get('/health', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
            $payload = json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR);
            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        });

        return $app;
    }
}
