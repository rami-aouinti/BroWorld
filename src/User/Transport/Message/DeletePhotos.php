<?php

declare(strict_types=1);

namespace App\User\Transport\Message;

use App\Announce\Domain\Entity\Property;

class DeletePhotos
{
    public function __construct(private readonly Property $property)
    {
    }

    public function getProperty(): Property
    {
        return $this->property;
    }
}
