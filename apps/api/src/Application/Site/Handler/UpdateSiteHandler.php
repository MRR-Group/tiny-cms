<?php

declare(strict_types=1);

namespace App\Application\Site\Handler;

use App\Application\Site\Command\UpdateSiteCommand;
use App\Domain\Site\Repository\SiteRepositoryInterface;
use App\Domain\Site\ValueObject\SiteId;

class UpdateSiteHandler
{
    public function __construct(
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    public function handle(UpdateSiteCommand $command): void
    {
        $siteId = SiteId::fromString($command->siteId);
        $site = $this->siteRepository->findById($siteId);

        if ($site === null) {
            throw new \InvalidArgumentException("Site not found");
        }

        $url = $this->normalizeUrl($command->url);
        $existingSite = $this->siteRepository->findByUrl($url);

        if ($existingSite !== null && !$existingSite->getId()->equals($siteId)) {
            throw new \InvalidArgumentException("Site with URL '{$url}' already exists");
        }

        $site->updateName($command->name);
        $site->updateUrl($url);
        $site->updateType($command->type);

        $this->siteRepository->save($site);
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);

        // Add protocol if missing
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = "https://" . $url;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $host = strtolower($parts["host"]);

        // Add www. if host has only 2 parts (e.g. example.com)
        if (!str_starts_with($host, "www.") && count(explode(".", $host)) === 2) {
            $host = "www." . $host;
        }

        $scheme = strtolower($parts["scheme"]);
        $path = $parts["path"] ?? "/";

        if (!str_ends_with($path, "/")) {
            $path .= "/";
        }

        $query = isset($parts["query"]) ? "?{$parts['query']}" : "";
        $fragment = isset($parts["fragment"]) ? "#{$parts['fragment']}" : "";

        return "{$scheme}://{$host}{$path}{$query}{$fragment}";
    }
}
