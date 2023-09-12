<?php

declare(strict_types=1);

namespace App\Quiz\Model\Entity;

class BaseEntity
{
    use TimestampableEntityTrait;
    public const STRING_DEFAULT_LENGTH = 255;
}
