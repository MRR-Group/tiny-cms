<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Site\Handler;

use App\Application\Site\Command\CreateSiteCommand;
use App\Application\Site\Handler\CreateSiteHandler;
use App\Domain\Shared\Clock\ClockInterface;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CreateSiteHandlerTest extends TestCase
{
    public function testHandleCreatesSiteWithNormalization(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $clock = $this->createMock(ClockInterface::class);
        $now = new \DateTimeImmutable();
        $clock->method("now")->willReturn($now);

        $handler = new CreateSiteHandler($siteRepository, $clock);

        // Input: "example.com" -> Output: "https://www.example.com/"
        $command = new CreateSiteCommand("My Site", "example.com", "static");

        $siteRepository->expects($this->once())
            ->method("save")
            ->with($this->callback(
                fn(Site $site) => $site->getUrl() === "https://www.example.com/",
            ));

        $handler->handle($command);
    }

    public function testHandleThrowsIfSiteAlreadyExists(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $clock = $this->createMock(ClockInterface::class);

        $existingSite = $this->createMock(Site::class);
        // Normalize search
        $siteRepository->method("findByUrl")->with("https://www.xd.pl/")->willReturn($existingSite);

        $handler = new CreateSiteHandler($siteRepository, $clock);
        $command = new CreateSiteCommand("My Site", "xd.pl", "static");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Site with URL 'https://www.xd.pl/' already exists");

        $handler->handle($command);
    }

    #[DataProvider("normalizationProvider")]
    public function testNormalization(string $inputUrl, string $expectedUrl): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $clock = $this->createMock(ClockInterface::class);
        $clock->method("now")->willReturn(new \DateTimeImmutable());

        $handler = new CreateSiteHandler($siteRepository, $clock);

        $siteRepository->expects($this->once())
            ->method("save")
            ->with($this->callback(function (Site $site) use ($expectedUrl) {
                $this->assertEquals($expectedUrl, $site->getUrl());

                return true;
            }));

        $handler->handle(new CreateSiteCommand("Site", $inputUrl, "static"));
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
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $clock = $this->createMock(ClockInterface::class);
        $clock->method("now")->willReturn(new \DateTimeImmutable());

        $handler = new CreateSiteHandler($siteRepository, $clock);

        // URL that returns false from parse_url (after https prepend)
        $command = new CreateSiteCommand("Site", "///", "static");

        $siteRepository->expects($this->once())
            ->method("save")
            ->with($this->callback(function (Site $site) {
                // Returns original URL with https prepended
                $this->assertEquals("https://///", $site->getUrl());

                return true;
            }));

        $handler->handle($command);
    }

    public function testHandleWithHttpProtocol(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $clock = $this->createMock(ClockInterface::class);
        $clock->method("now")->willReturn(new \DateTimeImmutable());

        $handler = new CreateSiteHandler($siteRepository, $clock);

        $command = new CreateSiteCommand("Site", "http://example.com", "static");

        $siteRepository->expects($this->once())
            ->method("save")
            ->with($this->callback(function (Site $site) {
                $this->assertEquals("http://www.example.com/", $site->getUrl());

                return true;
            }));

        $handler->handle($command);
    }

    public function testHandleWithCaretRegex(): void
    {
        $siteRepository = $this->createMock(SiteRepositoryInterface::class);
        $clock = $this->createMock(ClockInterface::class);
        $clock->method("now")->willReturn(new \DateTimeImmutable());

        $handler = new CreateSiteHandler($siteRepository, $clock);

        // URL containing http: inside but not at start
        // Without caret ^, regex matches inside and thinks protocol exists.
        // With caret ^, regex fails, prepends https://.
        $command = new CreateSiteCommand("Site", "example.com/foo?u=http://bar", "static");

        $siteRepository->expects($this->once())
            ->method("save")
            ->with($this->callback(function (Site $site) {
                // Correct behavior: prepends https://
                $this->assertEquals("https://www.example.com/foo/?u=http://bar", $site->getUrl());

                return true;
            }));

        $handler->handle($command);
    }
}
