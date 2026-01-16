<?php

declare(strict_types=1);

namespace App\Action;

use App\Service\VersionService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class HealthAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $versionService = new VersionService();

        $data = [
            "status" => "ok",
            "timestamp" => date("c"),
            "version" => $versionService->getVersion(),
        ];

        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus(200);
    }
}
