<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\UpdateSiteCommand;
use App\Application\Site\Handler\UpdateSiteHandler;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdateSiteHandler::class)]
class UpdateSiteHandlerTest extends TestCase
{
    private SiteRepositoryInterface&MockObject $repository;
    private UpdateSiteHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SiteRepositoryInterface::class);
        $this->handler = new UpdateSiteHandler($this->repository);
    }

    public function testHandleUpdatesSiteWithNormalization(): void
    {
        $id = SiteId::generate();
        // Input: "new-url.com" -> Output: "https://www.new-url.com/"
        $command = new UpdateSiteCommand(
            $id->toString(),
            "New Name",
            "new-url.com",
            SiteType::DYNAMIC,
        );

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);

        $site->expects($this->once())->method("updateUrl")->with("https://www.new-url.com/");
        $site->expects($this->once())->method("updateName")->with("New Name");
        $site->expects($this->once())->method("updateType")->with(SiteType::DYNAMIC);

        $this->repository->method("findById")->willReturn($site);
        // Duplicate check should return null or the same site
        $this->repository->method("findByUrl")->willReturn(null);
        $this->repository->expects($this->once())->method("save")->with($site);

        $this->handler->handle($command);
    }

    public function testHandleThrowsIfSiteNotFound(): void
    {
        $id = SiteId::generate();
        $command = new UpdateSiteCommand($id->toString(), "Name", "url.com", SiteType::STATIC);

        $this->repository->method("findById")->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site not found");

        $this->handler->handle($command);
    }

    public function testHandleThrowsIfUrlAlreadyTakenByAnotherSite(): void
    {
        $id = SiteId::generate();
        $otherId = SiteId::generate();
        $command = new UpdateSiteCommand($id->toString(), "Name", "taken.com", SiteType::STATIC);

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);
        $this->repository->method("findById")->willReturn($site);

        $otherSite = $this->createMock(Site::class);
        $otherSite->method("getId")->willReturn($otherId);
        $this->repository->method("findByUrl")->with("https://www.taken.com/")->willReturn($otherSite);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site with URL 'https://www.taken.com/' already exists");

        $this->handler->handle($command);
    }

    public function testHandleDoesNotThrowIfUrlTakenBySameSite(): void
    {
        $id = SiteId::generate();
        $command = new UpdateSiteCommand($id->toString(), "Name", "same.com", SiteType::STATIC);

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);
        $this->repository->method("findById")->willReturn($site);

        // Same site returned by findByUrl
        $this->repository->method("findByUrl")->with("https://www.same.com/")->willReturn($site);

        $site->expects($this->once())->method("updateUrl")->with("https://www.same.com/");

        $this->handler->handle($command);
    }

    #[DataProvider("normalizationProvider")]
    public function testNormalization(string $inputUrl, string $expectedUrl): void
    {
        $id = SiteId::generate();
        $command = new UpdateSiteCommand($id->toString(), "Name", $inputUrl, SiteType::STATIC);

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);
        $this->repository->method("findById")->willReturn($site);
        $this->repository->method("findByUrl")->willReturn(null);

        $site->expects($this->once())
            ->method("updateUrl")
            ->with($this->callback(function (string $url) use ($expectedUrl) {
                $this->assertEquals($expectedUrl, $url);

                return true;
            }));

        $this->handler->handle($command);
    }

    public static function normalizationProvider(): array
    {
        return [
            "no protocol, no www" => ["example.com", "https://www.example.com/"],
            "http protocol, no www" => ["http://example.com", "http://www.example.com/"],
            "https protocol, no www" => ["https://example.com", "https://www.example.com/"],
            "no protocol, with www" => ["www.example.com", "https://www.example.com/"],
            "with sub-sub-domain" => ["abc.def.example.com", "https://abc.def.example.com/"],
            "invalid host fallback" => ["https:///path", "https:///path"],
            "already normalized" => ["https://www.example.com/", "https://www.example.com/"],
            "uppercase protocol" => ["HTTPS://EXAMPLE.COM", "https://www.example.com/"],
            "another case" => ["SITE.INFO", "https://www.site.info/"],
            "with leading/trailing whitespace" => ["  example.com  ", "https://www.example.com/"],
            "with tabs" => ["\texample.com\t", "https://www.example.com/"],
            "domain with http text" => ["httpbin.org", "https://www.httpbin.org/"],
            "no protocol needed" => ["test.com", "https://www.test.com/"],
        ];
    }

    public function testHandleWithUnparseableUrl(): void
    {
        $id = SiteId::generate();
        // URL that should work with normalization
        $command = new UpdateSiteCommand($id->toString(), "Name", "///", SiteType::STATIC);

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);
        $this->repository->method("findById")->willReturn($site);
        $this->repository->method("findByUrl")->willReturn(null);

        $site->expects($this->once())
            ->method("updateUrl")
            ->with("https://///");
        $site->expects($this->once())->method("updateName");
        $site->expects($this->once())->method("updateType");
        $this->repository->expects($this->once())->method("save");

        $this->handler->handle($command);
    }

    public function testHandleWithHttpProtocol(): void
    {
        $id = SiteId::generate();
        $command = new UpdateSiteCommand($id->toString(), "Name", "http://example.com", SiteType::STATIC);

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);
        $this->repository->method("findById")->willReturn($site);
        $this->repository->method("findByUrl")->willReturn(null);

        $site->expects($this->once())
            ->method("updateUrl")
            ->with("http://www.example.com/");
        $site->expects($this->once())->method("updateName");
        $site->expects($this->once())->method("updateType");

        $this->handler->handle($command);
    }

    public function testHandleWithCaretRegex(): void
    {
        $id = SiteId::generate();
        $command = new UpdateSiteCommand($id->toString(), "Name", "example.com/foo?u=http://bar", SiteType::STATIC);

        $site = $this->createMock(Site::class);
        $site->method("getId")->willReturn($id);
        $this->repository->method("findById")->willReturn($site);
        $this->repository->method("findByUrl")->willReturn(null);

        $site->expects($this->once())
            ->method("updateUrl")
            ->with("https://www.example.com/foo/?u=http://bar");
        $site->expects($this->once())->method("updateName");
        $site->expects($this->once())->method("updateType");
        $this->repository->expects($this->once())->method("save");

        $this->handler->handle($command);
    }
}
