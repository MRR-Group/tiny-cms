<?php

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\EntityManagerFactory;
use App\Infrastructure\Security\Argon2PasswordHasher;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

if ($argc < 3) {
    echo "Usage: php create-admin.php <email> <password>\n";
    exit(1);
}

$emailStr = $argv[1];
$passwordStr = $argv[2];

$em = EntityManagerFactory::create();
$hasher = new Argon2PasswordHasher();

$email = new Email($emailStr);
$passwordHash = $hasher->hash($passwordStr);
$role = Role::admin();

$user = new User(UserId::generate(), $email, $role, $passwordHash);
$user->requirePasswordChange();

$em->persist($user);
$em->flush();

echo "Admin user created: {$emailStr}\n";
