<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Site;

use App\Application\Site\Command\AssignUserToSiteCommand;
use Psr\Http\Message\ServerRequestInterface;

class AssignUserToSiteRequest
{
    public static function fromPsr7(ServerRequestInterface $request): AssignUserToSiteCommand
    {
        $body = $request->getParsedBody();

        if (!isset($body['userId'], $body['siteId'])) {
            throw new \InvalidArgumentException('Missing required fields: userId, siteId');
        }

        return new AssignUserToSiteCommand(
            $body['userId'],
            $body['siteId']
        );
    }
}
