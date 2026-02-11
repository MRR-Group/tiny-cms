<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Site;

use App\Application\Site\Command\ReorderSiteSectionsCommand;
use Psr\Http\Message\ServerRequestInterface;

class ReorderSiteSectionsRequest
{
    public static function fromPsr7(ServerRequestInterface $request): ReorderSiteSectionsCommand
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

        if (!isset($body["sectionIds"]) || !is_array($body["sectionIds"])) {
            throw new \InvalidArgumentException("sectionIds must be an array");
        }

        foreach ($body["sectionIds"] as $sectionId) {
            if (!is_string($sectionId) || $sectionId === "") {
                throw new \InvalidArgumentException("Each section id must be a non-empty string");
            }
        }

        return new ReorderSiteSectionsCommand($siteId, $body["sectionIds"]);
    }
}
