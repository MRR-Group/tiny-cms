<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Site;

use App\Application\Site\Command\AddSiteSectionCommand;
use Psr\Http\Message\ServerRequestInterface;

class CreateSiteSectionRequest
{
    public static function fromPsr7(ServerRequestInterface $request): AddSiteSectionCommand
    {
        $body = $request->getParsedBody();

        if (!is_array($body)) {
            throw new \InvalidArgumentException("Invalid body");
        }

        $siteIdAttribute = $request->getAttribute("id");
        $siteIdFromBody = $body["siteId"] ?? null;
        $siteId = null;

        if (is_string($siteIdAttribute)) {
            $normalizedSiteId = trim($siteIdAttribute);

            if ($normalizedSiteId !== "") {
                $siteId = $normalizedSiteId;
            }
        }

        if ($siteId === null && is_string($siteIdFromBody)) {
            $normalizedSiteIdFromBody = trim($siteIdFromBody);

            if ($normalizedSiteIdFromBody !== "") {
                $siteId = $normalizedSiteIdFromBody;
            }
        }

        if ($siteId === null) {
            throw new \InvalidArgumentException("Site ID is required");
        }

        if (!isset($body["type"], $body["title"]) || !is_string($body["type"]) || !is_string($body["title"])) {
            throw new \InvalidArgumentException("Section type and title are required");
        }

        return new AddSiteSectionCommand(
            siteId: $siteId,
            type: $body["type"],
            title: $body["title"],
        );
    }
}
