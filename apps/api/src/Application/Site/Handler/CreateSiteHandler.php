<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\CreateSiteCommand;
use App\Domain\Shared\Clock\ClockInterface;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;

class CreateSiteHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private ClockInterface $clock,
    ) {}

    public function handle(CreateSiteCommand $command): SiteId
    {
        $id = SiteId::generate();
        $type = SiteType::from($command->type);
        $createdAt = $this->clock->now();

        $url = $this->normalizeUrl($command->url);
        
        if ($this->siteRepository->findByUrl($url)) {
            throw new \InvalidArgumentException("Site with URL '{$url}' already exists");
        }

        $site = new Site(
            $id,
            $command->name,
            $url,
            $type,
            $createdAt,
        );

        $this->siteRepository->save($site);

        return $id;
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        
        // Add protocol if missing
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);
        if ($parts === false || !isset($parts['host'])) {
            return $url;
        }

        $host = $parts['host'];
        // Add www. if host has only 2 parts (e.g. example.com)
        if (!str_starts_with($host, 'www.') && count(explode('.', $host)) === 2) {
            $host = 'www.' . $host;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $path = $parts['path'] ?? '/';
        if (!str_ends_with($path, '/')) {
            $path .= '/';
        }

        return "{$scheme}://{$host}{$path}";
    }
}
