<?php

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\ValueObject\Email;
use App\Domain\Auth\ValueObject\Role;
use App\Domain\Auth\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\EntityManagerFactory;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineUserRepository;
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

$settings = require __DIR__ . '/../config/settings.php';
$em = EntityManagerFactory::create($settings['settings']);
$repository = new DoctrineUserRepository($em);
$hasher = new Argon2PasswordHasher();

try {
    $email = new Email($emailStr);
    $existingUser = $repository->findByEmail($email);

    if ($existingUser) {
        $existingUser->changePassword($hasher->hash($passwordStr));
        $em->flush();
        echo "User {$emailStr} already exists. Password updated.\n";
    } else {
        $user = new User(UserId::generate(), $email, Role::admin(), $hasher->hash($passwordStr));
        $user->requirePasswordChange();
        $repository->save($user);
        echo "Admin user created: {$emailStr}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
