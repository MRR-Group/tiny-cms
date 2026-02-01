<?php

declare(strict_types=1);

namespace App\Domain\Shared\Util;

class UrlNormalizer
{
    public function normalize(string $url): string
    {
        $url = trim($url);
        $url = $this->ensureProtocol($url);
        
        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $host = strtolower($parts['host'] ?? '');
        
        $host = $this->ensureWww($host);
        
        return $this->buildUrl($parts, $host);
    }

    private function ensureProtocol(string $url): string
    {
        if (!preg_match('/^https?:\/\//i', $url)) {
            return "https://" . $url;
        }
        return $url;
    }

    private function ensureWww(string $host): string
    {
        if (!str_starts_with($host, "www.") && count(explode(".", $host)) === 2) {
            return "www." . $host;
        }
        return $host;
    }

    /**
     * @param array<string, int|string> $parts
     */
    private function buildUrl(array $parts, string $host): string
    {
        $scheme = isset($parts['scheme']) ? strtolower((string)$parts['scheme']) : 'https';
        $path = $parts['path'] ?? '/';
        
        if (!str_ends_with((string)$path, '/')) {
            $path .= '/';
        }

        $query = isset($parts['query']) ? "?{$parts['query']}" : "";
        $fragment = isset($parts['fragment']) ? "#{$parts['fragment']}" : "";

        return "{$scheme}://{$host}{$path}{$query}{$fragment}";
    }
}
