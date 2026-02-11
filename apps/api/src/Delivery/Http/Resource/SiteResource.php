<?php

declare(strict_types=1);

namespace App\Delivery\Http\Resource;

use App\Domain\Site\Entity\Site;

class SiteResource
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(Site $site): array
    {
        return [
            "id" => (string)$site->getId(),
            "name" => $site->getName(),
            "url" => $site->getUrl(),
            "type" => $site->getType()->value,
            "editorCount" => $site->getEditorCount(),
            "createdAt" => $site->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function toDetailArray(Site $site): array
    {
        $data = self::toArray($site);
        $data["editors"] = array_map(fn($user) => [
            "id" => (string)$user->getId(),
            "email" => (string)$user->getEmail(),
            "role" => $user->getRole()->toString(),
        ], $site->getUsers()->toArray());
        $data["sections"] = SiteSectionResource::collectionToArray($site->getSections());

        return $data;
    }

    /**
     * @param array<Site> $sites
     * @return array<int, array<string, mixed>>
     */
    public static function collectionToArray(array $sites): array
    {
        return array_map(fn(Site $site) => self::toArray($site), $sites);
    }
}
