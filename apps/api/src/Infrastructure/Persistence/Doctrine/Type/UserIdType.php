<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Auth\ValueObject\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;

class UserIdType extends GuidType
{
    public const NAME = "user_id";

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserId
    {
        $value = parent::convertToPHPValue($value, $platform);

        if ($value === null) {
            return null;
        }

        return new UserId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
