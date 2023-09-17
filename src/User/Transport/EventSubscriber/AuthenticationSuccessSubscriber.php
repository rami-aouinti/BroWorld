<?php

declare(strict_types=1);

namespace App\User\Transport\EventSubscriber;

use App\General\Domain\Doctrine\DBAL\Types\EnumLogLoginType;
use App\Log\Application\Service\LoginLoggerService;
use App\User\Domain\Repository\UserRepository;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

/**
 * Class AuthenticationSuccessSubscriber
 *
 * @package App\User
 */
class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    /**
     * @param LoginLoggerService $loginLoggerService
     * @param UserRepository $userRepository
     */
    public function __construct(
        private readonly LoginLoggerService $loginLoggerService,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationSuccessEvent::class => 'onAuthenticationSuccess'
        ];
    }

    /**
     * Method to log user successfully login to database.
     *
     * This method is called when following event is broadcast
     *  - lexik_jwt_authentication.on_authentication_success
     *
     * @throws Throwable
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $this->loginLoggerService
            ->setUser($this->userRepository->findOneBy([
                'email' => $event->getAuthenticationToken()->getUserIdentifier()
            ]))
            ->process(EnumLogLoginType::TYPE_SUCCESS);
    }
}
