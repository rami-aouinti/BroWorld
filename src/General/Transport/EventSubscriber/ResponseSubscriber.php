<?php

declare(strict_types=1);

namespace App\General\Transport\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class ResponseSubscriber
 *
 * @package App\General
 */
class ResponseSubscriber implements EventSubscriberInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ResponseEvent::class => [
                'onKernelResponse',
                10,
            ],
        ];
    }

    /**
     * Subscriber method to attach API version to every response.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        // Attach new header
        $event->getResponse()->headers->add([
            'X-API-VERSION' => 1,
        ]);
    }
}
