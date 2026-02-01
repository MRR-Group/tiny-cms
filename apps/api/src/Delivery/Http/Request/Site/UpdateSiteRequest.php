<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Site;

use App\Application\Site\Command\UpdateSiteCommand;
use App\Domain\Site\ValueObject\SiteType;
use Psr\Http\Message\ServerRequestInterface;

class UpdateSiteRequest
{
    public static function fromPsr7(ServerRequestInterface $request): UpdateSiteCommand
    {
        $body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $siteId = $request->getAttribute("id");

        if (!is_string($siteId) || empty($siteId)) {
            throw new \InvalidArgumentException("Site ID is required");
        }

        if (!isset($body["name"]) || !is_string($body["name"]) || empty($body["name"])) {
            throw new \InvalidArgumentException("Name is required");
        }

        if (!isset($body["url"]) || !is_string($body["url"]) || empty($body["url"])) {
            throw new \InvalidArgumentException("URL is required");
        }

        if (!isset($body["type"]) || !is_string($body["type"])) {
            throw new \InvalidArgumentException("Type is required");
        }

        try {
            $type = SiteType::from($body["type"]);
        } catch (\ValueError) {
            throw new \InvalidArgumentException("Invalid site type");
        }

        return new UpdateSiteCommand(
            siteId: $siteId,
            name: $body["name"],
            url: $body["url"],
            type: $type,
        );
    }
}
