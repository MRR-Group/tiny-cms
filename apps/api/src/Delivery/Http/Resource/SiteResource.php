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
            'id' => (string) $site->getId(),
            'name' => $site->getName(),
            'url' => $site->getUrl(),
            'type' => $site->getType()->value,
            'createdAt' => $site->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param Site[] $sites
     * @return array<int, array<string, mixed>>
     */
    public static function collectionToArray(array $sites): array
    {
        return array_map(fn(Site $site) => self::toArray($site), $sites);
    }
}
