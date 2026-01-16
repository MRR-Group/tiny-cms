<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Auth\ValueObject\Email;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class EmailType extends StringType
{
    public const NAME = "email";

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Email
    {
        $value = parent::convertToPHPValue($value, $platform);

        if ($value === null) {
            return null;
        }

        return new Email($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof Email ? $value->toString() : (string)$value;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
