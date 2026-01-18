<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Site;

use App\Application\Site\Command\CreateSiteCommand;
use Psr\Http\Message\ServerRequestInterface;

class CreateSiteRequest
{
    public static function fromPsr7(ServerRequestInterface $request): CreateSiteCommand
    {
        $body = $request->getParsedBody();

        if (!is_array($body)) {
            throw new \InvalidArgumentException('Invalid body');
        }

        if (!isset($body['name'], $body['url'], $body['type'])) {
            throw new \InvalidArgumentException('Missing required fields: name, url, type');
        }

        return new CreateSiteCommand(
            $body['name'],
            $body['url'],
            $body['type']
        );
    }
}
