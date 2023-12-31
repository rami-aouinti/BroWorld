<?php

declare(strict_types=1);

namespace App\Frontend\Infrastructure\Service;

use App\Frontend\Infrastructure\Message\TestMessage;
use App\Frontend\Infrastructure\Service\Interfaces\MessageServiceInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class MessageService
 *
 * @package App\Service
 */
class MessageService implements MessageServiceInterface
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly MessageBusInterface $bus
    ) {
    }

    /**
     * TODO: This is example for creating test message, you can delete it.
     */
    public function sendTestMessage(string $someId): self
    {
        $this->bus->dispatch(new Envelope(new TestMessage($someId)));

        return $this;
    }
}
