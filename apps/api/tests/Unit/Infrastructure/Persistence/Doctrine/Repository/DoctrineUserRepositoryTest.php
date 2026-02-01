<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class DoctrineUserRepositoryTest extends TestCase
{
    public function testFindByResetToken(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);

        $entityManager->expects($this->once())
            ->method("getRepository")
            ->with(User::class)
            ->willReturn($repository);

        $token = "some-token";
        $user = $this->createMock(User::class);

        $repository->expects($this->once())
            ->method("findOneBy")
            ->with(["resetToken" => $token])
            ->willReturn($user);

        $doctrineRepo = new DoctrineUserRepository($entityManager);

        $result = $doctrineRepo->findByResetToken($token);

        $this->assertSame($user, $result);
    }

    public function testSave(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $user = $this->createMock(User::class);

        $entityManager->expects($this->once())
            ->method("persist")
            ->with($user);

        $entityManager->expects($this->once())
            ->method("flush");

        $doctrineRepo = new DoctrineUserRepository($entityManager);
        $doctrineRepo->save($user);
    }

    public function testFindByEmail(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);

        $entityManager->expects($this->once())
            ->method("getRepository")
            ->with(User::class)
            ->willReturn($repository);

        $email = new Email("test@example.com");
        $user = $this->createMock(User::class);

        $repository->expects($this->once())
            ->method("findOneBy")
            ->with(["email" => $email])
            ->willReturn($user);

        $doctrineRepo = new DoctrineUserRepository($entityManager);

        $result = $doctrineRepo->findByEmail($email);

        $this->assertSame($user, $result);
    }

    public function testFindById(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $id = UserId::generate();
        $user = $this->createMock(User::class);

        // find($className, $id, ...)
        $entityManager->expects($this->once())
            ->method("find")
            ->with(User::class, $id)
            ->willReturn($user);

        $doctrineRepo = new DoctrineUserRepository($entityManager);

        $result = $doctrineRepo->findById($id);

        $this->assertSame($user, $result);
    }
}
