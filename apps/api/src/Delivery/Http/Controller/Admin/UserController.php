<?php

declare(strict_types=1);

namespace App\Delivery\Http\Controller\Admin;

use App\Application\Auth\Handler\ListUsersHandler;
use App\Application\Auth\Query\ListUsersQuery;
use App\Delivery\Http\Resource\UserResource;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController
{
    public function __construct(
        private readonly ListUsersHandler $listHandler,
    ) {}

    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $users = $this->listHandler->handle(new ListUsersQuery());
        $data = UserResource::collectionToArray($users);

        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response->withHeader("Content-Type", "application/json")->withStatus(200);
    }
}
