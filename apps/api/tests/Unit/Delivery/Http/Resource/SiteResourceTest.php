<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery\Http\Resource;

use App\Delivery\Http\Resource\SiteResource;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\TestCase;

class SiteResourceTest extends TestCase
{
    public function testToArray(): void
    {
        $id = SiteId::generate();
        $name = "Test Site";
        $url = "https://example.com";
        $type = SiteType::DYNAMIC;
        $createdAt = new \DateTimeImmutable("2023-01-01 12:00:00");

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);
        $site->method("getName")->willReturn($name);
        $site->method("getUrl")->willReturn($url);
        $site->method("getType")->willReturn($type);
        $site->method("getCreatedAt")->willReturn($createdAt);

        $result = SiteResource::toArray($site);

        $expected = [
            "id" => $id->toString(),
            "name" => $name,
            "url" => $url,
            "type" => $type->value,
            "createdAt" => $createdAt->format(\DateTimeInterface::ATOM),
        ];

        $this->assertSame($expected, $result);
    }

    public function testCollectionToArray(): void
    {
        $id = SiteId::generate();
        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);
        $site->method("getName")->willReturn("Name");
        $site->method("getUrl")->willReturn("Url");
        $site->method("getType")->willReturn(SiteType::STATIC);
        $site->method("getCreatedAt")->willReturn(new \DateTimeImmutable());

        $sites = [$site];

        $result = SiteResource::collectionToArray($sites);

        $this->assertCount(1, $result);
        $this->assertSame($id->toString(), $result[0]["id"]);
    }
}
