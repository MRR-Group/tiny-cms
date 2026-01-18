<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Site\Entity\Site;
use App\Domain\Site\ValueObject\SiteId;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineSiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DoctrineSiteRepositoryTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private DoctrineSiteRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = new DoctrineSiteRepository($this->entityManager);
    }

    public function testSavePersistsAndFlushes(): void
    {
        $site = $this->createMock(Site::class);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($site);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->repository->save($site);
    }

    public function testFindByIdReturnsSite(): void
    {
        $id = SiteId::generate();
        $site = $this->createMock(Site::class);

        $this->entityManager->expects($this->once())
            ->method('find')
            ->with(Site::class, $id)
            ->willReturn($site);

        $result = $this->repository->findById($id);

        $this->assertSame($site, $result);
    }

    public function testFindAllReturnsArray(): void
    {
        $doctrineRepository = $this->createMock(EntityRepository::class);
        $sites = [$this->createMock(Site::class)];

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Site::class)
            ->willReturn($doctrineRepository);

        $doctrineRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($sites);

        $result = $this->repository->findAll();

        $this->assertSame($sites, $result);
    }
}
