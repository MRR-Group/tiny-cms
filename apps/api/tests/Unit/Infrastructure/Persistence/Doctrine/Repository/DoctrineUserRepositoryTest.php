<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class DoctrineUserRepositoryTest extends TestCase
{
    public function testSavePersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $user = $this->createMock(User::class);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($user);

        $entityManager->expects($this->once())
            ->method('flush');

        $repository = new DoctrineUserRepository($entityManager);
        $repository->save($user);
    }

    public function testFindByEmailDelegatesToRepository(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $objectRepository = $this->createMock(EntityRepository::class);
        $user = $this->createMock(User::class);
        $email = new Email('test@example.com');

        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($objectRepository);

        $objectRepository->expects($this->once())
            ->method('findOneBy')
            // This strict expectation kills "ArrayItemRemoval" mutant which changes ["email" => $email] to []
            ->with(['email' => $email])
            ->willReturn($user);

        $repository = new DoctrineUserRepository($entityManager);
        $result = $repository->findByEmail($email);

        $this->assertSame($user, $result);
    }

    public function testFindByIdDelegatesToRepository(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $objectRepository = $this->createMock(EntityRepository::class);
        $user = $this->createMock(User::class);
        $id = UserId::generate();

        $entityManager->expects($this->once())
            ->method('find')
            ->with(User::class, $this->callback(fn($arg) => $arg instanceof UserId && $arg->toString() === $id->toString()))
            ->willReturn($user);

        $repository = new DoctrineUserRepository($entityManager);
        $result = $repository->findById($id);

        $this->assertSame($user, $result);
    }
}
