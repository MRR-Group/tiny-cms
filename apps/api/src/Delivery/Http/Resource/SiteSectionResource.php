<?php

declare(strict_types=1);

namespace App\Delivery\Http\Resource;

class SiteSectionResource
{
    /**
     * @param array<string, mixed> $section
     * @return array<string, mixed>
     */
    public static function toArray(array $section): array
    {
        return [
            "id" => $section["id"] ?? null,
            "type" => $section["type"] ?? null,
            "title" => $section["title"] ?? null,
            "data" => $section["data"] ?? [],
            "position" => $section["position"] ?? null,
            "createdAt" => $section["createdAt"] ?? null,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $sections
     * @return array<int, array<string, mixed>>
     */
    public static function collectionToArray(array $sections): array
    {
        return array_map(
            static fn(array $section): array => self::toArray($section),
            $sections,
        );
    }
}
