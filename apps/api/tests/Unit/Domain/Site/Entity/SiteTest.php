<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Site\Entity;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\UserId;
use App\Domain\Site\Entity\Site;
use App\Domain\Site\ValueObject\SiteId;
use App\Domain\Site\ValueObject\SiteType;
use PHPUnit\Framework\TestCase;

class SiteTest extends TestCase
{
    public function testRemoveUserRemovesUserFromSiteAndSiteFromUser(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );
        $user = $this->createMock(User::class);

        // Expect removeSite to be called on user when removed from site
        $user->expects($this->once())
            ->method("removeSite")
            ->with($site);

        $user->expects($this->once())
            ->method("addSite")
            ->with($site);

        $site->addUser($user);

        $site->removeUser($user);

        // Verify user is not in the collection
        $this->assertFalse($site->getUsers()->contains($user));
    }

    public function testGetters(): void
    {
        $id = SiteId::generate();
        $name = "Name";
        $url = "Url";
        $type = SiteType::STATIC;
        $createdAt = new \DateTimeImmutable();

        $site = new Site($id, $name, $url, $type, $createdAt);

        $this->assertSame($id, $site->getId());
        $this->assertSame($name, $site->getName());
        $this->assertSame($url, $site->getUrl());
        $this->assertSame($type, $site->getType());
        $this->assertSame($createdAt, $site->getCreatedAt());
        $this->assertSame(1, $site->getVersion());
    }

    public function testUpdateName(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Original Name",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $site->updateName("New Name");

        $this->assertSame("New Name", $site->getName());
    }

    public function testUpdateUrl(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $site->updateUrl("http://newurl.com");

        $this->assertSame("http://newurl.com", $site->getUrl());
    }

    public function testUpdateType(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $site->updateType(SiteType::DYNAMIC);

        $this->assertSame(SiteType::DYNAMIC, $site->getType());
    }

    public function testGetEditorCount(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $this->assertSame(0, $site->getEditorCount());

        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);

        $site->addUser($user1);
        $this->assertSame(1, $site->getEditorCount());

        $site->addUser($user2);
        $this->assertSame(2, $site->getEditorCount());
    }

    public function testAddTextSectionStoresSectionWithOrder(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $section = $site->addSection("text", "Hero", ["value" => "Main section content"]);
        $sections = $site->getSections();

        $this->assertCount(1, $sections);
        $this->assertSame($section["id"], $sections[0]["id"]);
        $this->assertSame("text", $sections[0]["type"]);
        $this->assertSame("Hero", $sections[0]["title"]);
        $this->assertSame(0, $sections[0]["position"]);
        $this->assertArrayHasKey("createdAt", $sections[0]);
    }

    public function testAddSectionStoresStructuredData(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $section = $site->addSection("contact", "Contact Us", [
            "items" => [
                ["type" => "email", "value" => "john@example.com"],
            ],
        ]);

        $this->assertSame("contact", $section["type"]);
        $this->assertSame("Contact Us", $section["title"]);
        $this->assertSame("email", $section["data"]["items"][0]["type"]);
    }

    public function testReorderSectionsUpdatesPosition(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $first = $site->addSection("text", "First", ["value" => "Content A"]);
        $second = $site->addSection("text", "Second", ["value" => "Content B"]);

        $site->reorderSections([$second["id"], $first["id"]]);
        $sections = $site->getSections();

        $this->assertSame($second["id"], $sections[0]["id"]);
        $this->assertSame(0, $sections[0]["position"]);
        $this->assertSame($first["id"], $sections[1]["id"]);
        $this->assertSame(1, $sections[1]["position"]);
    }

    public function testGetSectionsSortsNumericStringPositionsNumerically(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [
                ["id" => "sec-1", "type" => "text", "title" => "A", "position" => "10", "data" => []],
                ["id" => "sec-2", "type" => "text", "title" => "B", "position" => "2", "data" => []],
            ],
        );

        $sections = $site->getSections();

        $this->assertSame("sec-2", $sections[0]["id"]);
        $this->assertSame("sec-1", $sections[1]["id"]);
    }

    public function testHasAssignedUserReturnsTrueOnlyForAssignedUser(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $assignedUser = $this->createMock(User::class);
        $assignedUserId = "00000000-0000-0000-0000-000000000123";
        $assignedUser->method("getId")->willReturn(UserId::fromString($assignedUserId));

        $site->addUser($assignedUser);

        $this->assertTrue($site->hasAssignedUser($assignedUserId));
        $this->assertFalse($site->hasAssignedUser("00000000-0000-0000-0000-000000000999"));
    }

    public function testUpdateAndRemoveSection(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $first = $site->addSection("image", "Gallery", ["images" => ["a.jpg"]]);
        $second = $site->addSection("news", "News", ["items" => []]);

        $updated = $site->updateSection($first["id"], "Gallery Updated", ["images" => ["b.jpg", "c.jpg"]]);
        $this->assertSame("Gallery Updated", $updated["title"]);
        $this->assertSame("b.jpg", $updated["data"]["images"][0]);

        $site->removeSection($first["id"]);
        $sections = $site->getSections();

        $this->assertCount(1, $sections);
        $this->assertSame($second["id"], $sections[0]["id"]);
        $this->assertSame(0, $sections[0]["position"]);
    }

    public function testSectionItemLifecycle(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $section = $site->addSection("news", "Updates", ["items" => []]);

        $created = $site->addSectionItem($section["id"], ["title" => "Item A"]);
        $this->assertSame("Item A", $created["title"]);
        $this->assertArrayHasKey("createdAt", $created);

        $items = $site->listSectionItems($section["id"]);
        $this->assertCount(1, $items);
        $this->assertSame($created["id"], $items[0]["id"]);

        $updated = $site->updateSectionItem($section["id"], $created["id"], ["title" => "Item B"]);
        $this->assertSame($created["id"], $updated["id"]);
        $this->assertSame("Item B", $updated["title"]);
        $this->assertArrayHasKey("updatedAt", $updated);

        $site->removeSectionItem($section["id"], $created["id"]);
        $this->assertSame([], $site->listSectionItems($section["id"]));
    }

    public function testAddSectionItemRejectsReservedFields(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $section = $site->addSection("news", "Updates", ["items" => []]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data contains reserved field: id");
        $site->addSectionItem($section["id"], ["id" => "client-id", "title" => "Item A"]);
    }

    public function testUpdateSectionItemDoesNotAllowReservedFields(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $section = $site->addSection("news", "Updates", ["items" => []]);
        $created = $site->addSectionItem($section["id"], ["title" => "Item A"]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data contains reserved field: updatedAt");
        $site->updateSectionItem($section["id"], $created["id"], ["updatedAt" => "forced", "title" => "Item B"]);
    }

    public function testAddSectionItemRejectsEmptyObjectPayload(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $section = $site->addSection("news", "Updates", ["items" => []]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Item data must be a non-empty object");
        $site->addSectionItem($section["id"], []);
    }

    public function testUpdateSectionItemPreservesExistingFieldsAndNoNestedItemShape(): void
    {
        $createdAt = (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM);

        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [[
                "id" => "sec-1",
                "type" => "news",
                "title" => "Updates",
                "position" => 0,
                "data" => [
                    "items" => [[
                        "id" => "item-1",
                        "title" => "Before",
                        "createdAt" => $createdAt,
                    ]],
                ],
            ]],
        );

        $updated = $site->updateSectionItem("sec-1", "item-1", ["title" => "After"]);

        $this->assertSame("item-1", $updated["id"]);
        $this->assertSame("After", $updated["title"]);
        $this->assertSame($createdAt, $updated["createdAt"]);
        $this->assertArrayHasKey("updatedAt", $updated);
        $this->assertArrayNotHasKey(0, $updated);

        $items = $site->listSectionItems("sec-1");
        $this->assertArrayNotHasKey(0, $items[0]);
        $this->assertSame($createdAt, $items[0]["createdAt"]);
    }

    public function testListSectionItemsIgnoresNonArrayEntries(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [[
                "id" => "sec-1",
                "type" => "news",
                "title" => "Updates",
                "position" => 0,
                "data" => ["items" => ["invalid", ["id" => "item-1", "title" => "Valid"]]],
            ]],
        );

        $items = $site->listSectionItems("sec-1");

        $this->assertCount(1, $items);
        $this->assertSame("item-1", $items[0]["id"]);
    }

    public function testUpdateSectionItemSkipsInvalidEntriesBeforeMatch(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [[
                "id" => "sec-1",
                "type" => "news",
                "title" => "Updates",
                "position" => 0,
                "data" => ["items" => ["invalid", ["id" => "item-1", "title" => "Old"]]],
            ]],
        );

        $updated = $site->updateSectionItem("sec-1", "item-1", ["title" => "New"]);

        $this->assertSame("item-1", $updated["id"]);
        $this->assertSame("New", $updated["title"]);
    }

    public function testSectionItemOperationsThrowWhenTargetMissing(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section not found");
        $site->addSectionItem("missing", ["title" => "x"]);
    }

    public function testUpdateAndRemoveSectionItemThrowWhenItemMissing(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );
        $section = $site->addSection("news", "Updates", ["items" => []]);

        try {
            $site->updateSectionItem($section["id"], "missing-item", ["title" => "x"]);
            self::fail("Expected updateSectionItem to throw");
        } catch (\InvalidArgumentException $e) {
            $this->assertSame("Section item not found", $e->getMessage());
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section item not found");
        $site->removeSectionItem($section["id"], "missing-item");
    }

    public function testRemoveSectionItemThrowsWhenOnlyDifferentArrayItemsExist(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [[
                "id" => "sec-1",
                "type" => "news",
                "title" => "Updates",
                "position" => 0,
                "data" => ["items" => [["id" => "other", "title" => "Other"]]],
            ]],
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section item not found");
        $site->removeSectionItem("sec-1", "missing-item");
    }

    public function testUpdateAndRemoveSectionItemThrowWhenSectionMissing(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        try {
            $site->updateSectionItem("missing", "item-1", ["title" => "x"]);
            self::fail("Expected updateSectionItem to throw");
        } catch (\InvalidArgumentException $e) {
            $this->assertSame("Section not found", $e->getMessage());
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section not found");
        $site->removeSectionItem("missing", "item-1");
    }

    public function testSectionValidationErrors(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        try {
            $site->addSection("   ", "Hero", []);
            self::fail("Expected addSection to throw on empty type");
        } catch (\InvalidArgumentException $e) {
            $this->assertSame("Section type is required", $e->getMessage());
        }

        try {
            $site->addSection("text", "   ", []);
            self::fail("Expected addSection to throw on empty title");
        } catch (\InvalidArgumentException $e) {
            $this->assertSame("Section title is required", $e->getMessage());
        }

        $section = $site->addSection("text", "Hero", ["value" => "x"]);

        try {
            $site->updateSection($section["id"], "   ", ["value" => "x"]);
            self::fail("Expected updateSection to throw on empty title");
        } catch (\InvalidArgumentException $e) {
            $this->assertSame("Section title is required", $e->getMessage());
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section not found");
        $site->updateSection("missing", "Hero", []);
    }

    public function testSectionAndItemOperationsHandleNonFirstMatches(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $first = $site->addSection("text", "First", ["items" => []]);
        $second = $site->addSection("text", "Second", ["items" => []]);

        $item = $site->addSectionItem($second["id"], ["title" => "Second Item"]);
        $items = $site->listSectionItems($second["id"]);
        $this->assertSame($item["id"], $items[0]["id"]);

        $updated = $site->updateSectionItem($second["id"], $item["id"], ["title" => "Changed"]);
        $this->assertSame("Changed", $updated["title"]);

        $site->removeSectionItem($second["id"], $item["id"]);
        $this->assertSame([], $site->listSectionItems($second["id"]));

        $site->removeSection($first["id"]);
        $remaining = $site->getSections();
        $this->assertSame($second["id"], $remaining[0]["id"]);
    }

    public function testSectionAndItemLookupSupportsNumericIdentifiersStoredAsIntegers(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [[
                "id" => 101,
                "type" => "news",
                "title" => "Updates",
                "position" => 0,
                "data" => ["items" => [["id" => 202, "title" => "Old"]]],
            ]],
        );

        $section = $site->updateSection("101", "Updated", ["items" => [["id" => 202, "title" => "Old"]]]);
        $this->assertSame("Updated", $section["title"]);

        $items = $site->listSectionItems("101");
        $this->assertSame(202, $items[0]["id"]);

        $updated = $site->updateSectionItem("101", "202", ["title" => "New"]);
        $this->assertSame("202", $updated["id"]);

        $site->removeSectionItem("101", "202");
        $this->assertSame([], $site->listSectionItems("101"));

        $site->removeSection("101");
        $this->assertSame([], $site->getSections());
    }

    public function testUpdateSectionCanMatchSecondSectionWhenFirstDoesNotMatch(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $site->addSection("text", "First", []);
        $second = $site->addSection("text", "Second", []);

        $updated = $site->updateSection($second["id"], "Second Updated", []);

        $this->assertSame("Second Updated", $updated["title"]);
    }

    public function testAddSectionItemPreservesExistingItemsAndReindexes(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [[
                "id" => 101,
                "type" => "news",
                "title" => "Updates",
                "position" => 0,
                "data" => ["items" => [5 => ["id" => "existing", "title" => "Existing"]]],
            ]],
        );

        $site->addSectionItem("101", ["title" => "New"]);
        $sections = $site->getSections();

        $this->assertCount(2, $sections[0]["data"]["items"]);
        $this->assertSame([0, 1], array_keys($sections[0]["data"]["items"]));
        $this->assertSame("existing", $sections[0]["data"]["items"][0]["id"]);
    }

    public function testUpdateSectionItemUpdatesMatchingItemAndReindexesItems(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [[
                "id" => "sec-1",
                "type" => "news",
                "title" => "Updates",
                "position" => 0,
                "data" => [
                    "items" => [
                        5 => ["id" => "wrong", "title" => "Wrong"],
                        9 => ["id" => "target", "title" => "Target"],
                    ],
                ],
            ]],
        );

        $site->updateSectionItem("sec-1", "target", ["title" => "Updated"]);
        $sections = $site->getSections();

        $this->assertSame([0, 1], array_keys($sections[0]["data"]["items"]));
        $this->assertSame("wrong", $sections[0]["data"]["items"][0]["id"]);
        $this->assertSame("target", $sections[0]["data"]["items"][1]["id"]);
        $this->assertSame("Updated", $sections[0]["data"]["items"][1]["title"]);
    }

    public function testRemoveSectionItemRemovesOnlyMatchAndKeepsOtherItems(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [[
                "id" => "sec-1",
                "type" => "news",
                "title" => "Updates",
                "position" => 0,
                "data" => [
                    "items" => [
                        5 => ["id" => "wrong", "title" => "Wrong"],
                        9 => ["id" => 77, "title" => "Target"],
                        11 => ["id" => "tail", "title" => "Tail"],
                    ],
                ],
            ]],
        );

        $site->removeSectionItem("sec-1", "77");
        $sections = $site->getSections();

        $remainingIds = array_map(
            static fn(array $item): string => (string)$item["id"],
            array_values($sections[0]["data"]["items"]),
        );

        $this->assertSame(["wrong", "tail"], $remainingIds);
    }

    public function testReorderSectionsUsesStoredStringIds(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [
                ["id" => "11", "type" => "text", "title" => "A", "position" => 0, "data" => []],
                ["id" => "22", "type" => "text", "title" => "B", "position" => 1, "data" => []],
            ],
        );

        $site->reorderSections(["22", "11"]);
        $sections = $site->getSections();

        $this->assertSame("22", $sections[0]["id"]);
        $this->assertSame("11", $sections[1]["id"]);
    }

    public function testReorderSectionsThrowsWhenStoredSectionIdIsInvalid(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [["id" => 11, "type" => "text", "title" => "A", "position" => 0, "data" => []]],
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Stored section id must be a non-empty string");

        $site->reorderSections(["11"]);
    }

    public function testAddSectionItemNormalizesNonArrayItemsContainer(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
            [[
                "id" => "sec-1",
                "type" => "news",
                "title" => "Updates",
                "position" => 0,
                "data" => ["items" => "invalid"],
            ]],
        );

        $item = $site->addSectionItem("sec-1", ["title" => "Item A"]);
        $items = $site->listSectionItems("sec-1");

        $this->assertSame($item["id"], $items[0]["id"]);
        $this->assertCount(1, $items);
    }

    public function testReorderSectionsValidationErrors(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $first = $site->addSection("text", "First", []);
        $second = $site->addSection("text", "Second", []);

        try {
            $site->reorderSections([$first["id"]]);
            self::fail("Expected reorderSections to throw on incomplete payload");
        } catch (\InvalidArgumentException $e) {
            $this->assertSame("Reorder payload must include all sections", $e->getMessage());
        }

        try {
            $site->reorderSections(["missing", $second["id"]]);
            self::fail("Expected reorderSections to throw on unknown id");
        } catch (\InvalidArgumentException $e) {
            $this->assertSame("Reorder payload contains unknown section id", $e->getMessage());
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Reorder payload contains unknown section id");
        $site->reorderSections([$first["id"], $first["id"]]);
    }

    public function testRemoveSectionThrowsWhenMissing(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section not found");
        $site->removeSection("missing");
    }

    public function testListSectionItemsThrowsWhenSectionMissing(): void
    {
        $site = new Site(
            SiteId::generate(),
            "Test Site",
            "http://example.com",
            SiteType::STATIC,
            new \DateTimeImmutable(),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Section not found");
        $site->listSectionItems("missing");
    }
}
