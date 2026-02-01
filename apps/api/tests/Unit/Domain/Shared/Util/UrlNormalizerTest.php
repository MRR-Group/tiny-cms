<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Shared\Util;

use App\Domain\Shared\Util\UrlNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UrlNormalizerTest extends TestCase
{
    private UrlNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new UrlNormalizer();
    }

    #[DataProvider("normalizationProvider")]
    public function testNormalize(string $inputUrl, string $expectedUrl): void
    {
        $this->assertEquals($expectedUrl, $this->normalizer->normalize($inputUrl));
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
            "path only" => ["https:///path/only", "https:///path/only"],
            "completely invalid" => ["ht!tp://invalid", "https://ht!tp//invalid/"], // Matches parse_url behavior
        ];
    }
}
