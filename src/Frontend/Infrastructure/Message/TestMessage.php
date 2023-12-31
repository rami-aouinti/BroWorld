<?php

declare(strict_types=1);

namespace App\Frontend\Infrastructure\Message;

use App\Frontend\Infrastructure\Message\Interfaces\MessageHighInterface;

/**
 * Class TestMessage
 * TODO: This is message example, you can delete it.
 *
 * @package App\Message
 */
class TestMessage implements MessageHighInterface
{
    public function __construct(
        private readonly string $someId
    ) {
    }

    public function getSomeId(): string
    {
        return $this->someId;
    }
}
