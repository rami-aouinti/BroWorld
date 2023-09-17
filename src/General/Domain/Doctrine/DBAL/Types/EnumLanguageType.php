<?php

declare(strict_types=1);

namespace App\General\Domain\Doctrine\DBAL\Types;

use App\General\Domain\Enum\Language;

/**
 * Class EnumLanguageType
 *
 * @package App\General
 */
class EnumLanguageType extends RealEnumType
{
    protected static string $name = Types::ENUM_LANGUAGE;
    protected static string $enum = Language::class;
}
