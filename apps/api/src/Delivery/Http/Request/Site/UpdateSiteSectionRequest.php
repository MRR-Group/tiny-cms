<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Site;

use App\Application\Site\Command\UpdateSiteSectionCommand;
use Psr\Http\Message\ServerRequestInterface;

class UpdateSiteSectionRequest
{
    public static function fromPsr7(ServerRequestInterface $request): UpdateSiteSectionCommand
    {
        $siteId = $request->getAttribute("id");
        $sectionId = $request->getAttribute("sectionId");

        if (!is_string($siteId) || $siteId === "") {
            throw new \InvalidArgumentException("Site ID is required");
        }

        if (!is_string($sectionId) || $sectionId === "") {
            throw new \InvalidArgumentException("Section ID is required");
        }

        $body = $request->getParsedBody();

        if (!is_array($body)) {
            throw new \InvalidArgumentException("Invalid body");
        }

        if (!isset($body["title"]) || !is_string($body["title"])) {
            throw new \InvalidArgumentException("Section title is required");
        }

        $data = $body["data"] ?? [];

        if (!is_array($data)) {
            throw new \InvalidArgumentException("Section data must be an object");
        }

        return new UpdateSiteSectionCommand($siteId, $sectionId, $body["title"], $data);
    }
}
