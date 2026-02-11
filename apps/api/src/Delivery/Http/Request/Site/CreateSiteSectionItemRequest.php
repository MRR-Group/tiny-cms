<?php

declare(strict_types=1);

namespace App\Delivery\Http\Request\Site;

use App\Application\Site\Command\AddSiteSectionItemCommand;
use Psr\Http\Message\ServerRequestInterface;

class CreateSiteSectionItemRequest
{
    public static function fromPsr7(ServerRequestInterface $request): AddSiteSectionItemCommand
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

        if (!is_array($body) || !isset($body["data"]) || !is_array($body["data"])) {
            throw new \InvalidArgumentException("Item data is required");
        }

        $data = $body["data"];

        if (array_is_list($data) || $data === []) {
            throw new \InvalidArgumentException("Item data must be a non-empty object");
        }

        foreach (["id", "createdAt", "updatedAt"] as $reservedField) {
            if (array_key_exists($reservedField, $data)) {
                throw new \InvalidArgumentException(sprintf("Item data contains reserved field: %s", $reservedField));
            }
        }

        return new AddSiteSectionItemCommand($siteId, $sectionId, $data);
    }
}
