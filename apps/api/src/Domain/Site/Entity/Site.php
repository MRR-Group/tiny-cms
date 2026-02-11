<?php

declare(strict_types=1);

namespace App\Domain\Site\Entity;

use App\Domain\Auth\Entity\User;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;

class Site
{
    /** @var Collection<int, User> */
    private Collection $users;

    private int $version = 1;

    /**
     * @param array<int, array<string, mixed>> $sections
     */
    public function __construct(
        private readonly SiteId $id,
        private string $name,
        private string $url,
        private SiteType $type,
        private \DateTimeImmutable $createdAt,
        /** @var array<int, array<string, mixed>> */
        private array $sections = [],
    ) {
        $this->users = new ArrayCollection();
    }

    public function getId(): SiteId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getType(): SiteType
    {
        return $this->type;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addSite($this);
        }
    }

    public function removeUser(User $user): void
    {
        if ($this->users->removeElement($user)) {
            $user->removeSite($this);
        }
    }

    public function updateName(string $name): void
    {
        $this->name = $name;
    }

    public function updateUrl(string $url): void
    {
        $this->url = $url;
    }

    public function updateType(SiteType $type): void
    {
        $this->type = $type;
    }

    public function getEditorCount(): int
    {
        return $this->users->count();
    }

    public function hasAssignedUser(string $userId): bool
    {
        foreach ($this->users as $user) {
            if ((string)$user->getId() === $userId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSections(): array
    {
        usort(
            $this->sections,
            static fn(array $left, array $right): int => $left["position"] <=> $right["position"],
        );

        return $this->sections;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function addSection(
        string $type,
        string $title,
        array $data = [],
    ): array {
        $normalizedType = trim($type);
        $normalizedTitle = trim($title);

        if ($normalizedType === "") {
            throw new \InvalidArgumentException("Section type is required");
        }

        if ($normalizedTitle === "") {
            throw new \InvalidArgumentException("Section title is required");
        }

        $section = [
            "id" => Uuid::uuid4()->toString(),
            "type" => $normalizedType,
            "title" => $normalizedTitle,
            "data" => $data,
            "position" => count($this->sections),
            "createdAt" => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        $this->sections[] = $section;

        return $section;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function updateSection(string $sectionId, string $title, array $data): array
    {
        $normalizedTitle = trim($title);

        if ($normalizedTitle === "") {
            throw new \InvalidArgumentException("Section title is required");
        }

        foreach ($this->sections as $index => $section) {
            if ((string)$section["id"] !== $sectionId) {
                continue;
            }

            $section["title"] = $normalizedTitle;
            $section["data"] = $data;
            $this->sections[$index] = $section;

            return $section;
        }

        throw new \InvalidArgumentException("Section not found");
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listSectionItems(string $sectionId): array
    {
        $section = $this->findSectionById($sectionId);
        $data = is_array($section["data"] ?? null) ? $section["data"] : [];
        $items = $data["items"] ?? [];

        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, static fn(mixed $item): bool => is_array($item)));
    }

    /**
     * @param array<string, mixed> $itemData
     * @return array<string, mixed>
     */
    public function addSectionItem(string $sectionId, array $itemData): array
    {
        $this->assertValidItemData($itemData);

        foreach ($this->sections as $index => $section) {
            if ((string)$section["id"] !== $sectionId) {
                continue;
            }

            $data = is_array($section["data"] ?? null) ? $section["data"] : [];
            $items = $data["items"] ?? [];

            if (!is_array($items)) {
                $items = [];
            }

            $item = [
                ...$itemData,
                "id" => Uuid::uuid4()->toString(),
                "createdAt" => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ];

            $items[] = $item;
            $data["items"] = array_values($items);
            $section["data"] = $data;
            $this->sections[$index] = $section;

            return $item;
        }

        throw new \InvalidArgumentException("Section not found");
    }

    /**
     * @param array<string, mixed> $itemData
     * @return array<string, mixed>
     */
    public function updateSectionItem(string $sectionId, string $itemId, array $itemData): array
    {
        $this->assertValidItemData($itemData);

        foreach ($this->sections as $sectionIndex => $section) {
            if ((string)$section["id"] !== $sectionId) {
                continue;
            }

            $data = is_array($section["data"] ?? null) ? $section["data"] : [];
            $items = $data["items"] ?? [];

            if (!is_array($items)) {
                $items = [];
            }

            foreach ($items as $itemIndex => $item) {
                if (!is_array($item) || (string)($item["id"] ?? "") !== $itemId) {
                    continue;
                }

                $updated = [
                    ...$item,
                    ...$itemData,
                    "id" => $itemId,
                    "updatedAt" => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
                ];

                $items[$itemIndex] = $updated;
                $data["items"] = array_values($items);
                $section["data"] = $data;
                $this->sections[$sectionIndex] = $section;

                return $updated;
            }

            throw new \InvalidArgumentException("Section item not found");
        }

        throw new \InvalidArgumentException("Section not found");
    }

    public function removeSectionItem(string $sectionId, string $itemId): void
    {
        foreach ($this->sections as $sectionIndex => $section) {
            if ((string)$section["id"] !== $sectionId) {
                continue;
            }

            $data = is_array($section["data"] ?? null) ? $section["data"] : [];
            $items = $data["items"] ?? [];

            if (!is_array($items)) {
                $items = [];
            }

            $removed = false;
            $remaining = [];

            foreach ($items as $item) {
                if (is_array($item) && (string)($item["id"] ?? "") === $itemId) {
                    $removed = true;

                    continue;
                }
                $remaining[] = $item;
            }

            if (!$removed) {
                throw new \InvalidArgumentException("Section item not found");
            }

            $data["items"] = $remaining;
            $section["data"] = $data;
            $this->sections[$sectionIndex] = $section;

            return;
        }

        throw new \InvalidArgumentException("Section not found");
    }

    public function removeSection(string $sectionId): void
    {
        $removed = false;
        $remaining = [];

        foreach ($this->sections as $section) {
            if ((string)$section["id"] === $sectionId) {
                $removed = true;

                continue;
            }

            $remaining[] = $section;
        }

        if (!$removed) {
            throw new \InvalidArgumentException("Section not found");
        }

        $this->sections = array_map(
            static fn(array $section, int $index): array => [...$section, "position" => $index],
            $remaining,
            array_keys($remaining),
        );
    }

    /**
     * @param array<int, string> $orderedSectionIds
     */
    public function reorderSections(array $orderedSectionIds): void
    {
        if (count($orderedSectionIds) !== count($this->sections)) {
            throw new \InvalidArgumentException("Reorder payload must include all sections");
        }

        $sectionsById = [];

        foreach ($this->sections as $section) {
            $sectionId = $section["id"] ?? null;

            if (!is_string($sectionId) || $sectionId === "") {
                throw new \InvalidArgumentException("Stored section id must be a non-empty string");
            }

            $sectionsById[$sectionId] = $section;
        }

        $reordered = [];

        foreach ($orderedSectionIds as $position => $sectionId) {
            if (!isset($sectionsById[$sectionId])) {
                throw new \InvalidArgumentException("Reorder payload contains unknown section id");
            }

            $section = $sectionsById[$sectionId];
            $section["position"] = $position;
            $reordered[] = $section;
            unset($sectionsById[$sectionId]);
        }

        $this->sections = $reordered;
    }

    /**
     * @return array<string, mixed>
     */
    private function findSectionById(string $sectionId): array
    {
        foreach ($this->sections as $section) {
            if ((string)$section["id"] === $sectionId) {
                return $section;
            }
        }

        throw new \InvalidArgumentException("Section not found");
    }

    /**
     * @param array<string, mixed> $itemData
     */
    private function assertValidItemData(array $itemData): void
    {
        if ($itemData === []) {
            throw new \InvalidArgumentException("Item data must be a non-empty object");
        }

        foreach (["id", "createdAt", "updatedAt"] as $reservedField) {
            if (array_key_exists($reservedField, $itemData)) {
                throw new \InvalidArgumentException(sprintf("Item data contains reserved field: %s", $reservedField));
            }
        }
    }
}
