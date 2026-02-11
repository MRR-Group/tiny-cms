<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Resource;

use App\Delivery\Http\Resource\SiteSectionResource;
use PHPUnit\Framework\TestCase;

class SiteSectionResourceTest extends TestCase
{
    public function testToArrayMapsKnownFields(): void
    {
        $section = [
            "id" => "sec-1",
            "type" => "news",
            "title" => "Updates",
            "data" => ["items" => []],
            "position" => 3,
            "createdAt" => "2024-01-01T10:00:00+00:00",
        ];

        $result = SiteSectionResource::toArray($section);

        $this->assertSame("sec-1", $result["id"]);
        $this->assertSame("news", $result["type"]);
        $this->assertSame("Updates", $result["title"]);
        $this->assertSame(["items" => []], $result["data"]);
        $this->assertSame(3, $result["position"]);
        $this->assertSame("2024-01-01T10:00:00+00:00", $result["createdAt"]);
    }

    public function testToArrayUsesDefaultsForMissingFields(): void
    {
        $result = SiteSectionResource::toArray([]);

        $this->assertNull($result["id"]);
        $this->assertNull($result["type"]);
        $this->assertNull($result["title"]);
        $this->assertSame([], $result["data"]);
        $this->assertNull($result["position"]);
        $this->assertNull($result["createdAt"]);
    }

    public function testCollectionToArrayMapsEachSection(): void
    {
        $sections = [
            ["id" => "sec-1", "type" => "text", "title" => "Intro"],
            ["id" => "sec-2", "type" => "image", "title" => "Hero"],
        ];

        $result = SiteSectionResource::collectionToArray($sections);

        $this->assertCount(2, $result);
        $this->assertSame("sec-1", $result[0]["id"]);
        $this->assertSame("sec-2", $result[1]["id"]);
        $this->assertSame([], $result[0]["data"]);
    }
}
