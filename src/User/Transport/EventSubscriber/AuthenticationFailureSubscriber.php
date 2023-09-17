<?php

declare(strict_types=1);

namespace App\User\Transport\EventSubscriber;

use App\General\Domain\Doctrine\DBAL\Types\EnumLogLoginType;
use App\Log\Application\Service\LoginLoggerService;
use App\User\Domain\Repository\UserRepository;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

/**
 * Class AuthenticationFailureSubscriber
 *
 * @package App\User
 */
class AuthenticationFailureSubscriber implements EventSubscriberInterface
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
            DefaultAuthenticationFailureHandler::class => 'onAuthenticationFailure',
            LoginFailureEvent::class => 'onAuthenticationFailure',
        ];
    }

    /**
     * Method to log login failures to database.
     *
     * This method is called when following event is broadcast;
     *  - \Lexik\Bundle\JWTAuthenticationBundle\Events::AUTHENTICATION_FAILURE
     *
     * @throws Throwable
     */
    public function onAuthenticationFailure(LoginFailureEvent $event): void
    {
        $token = $event->getException()->getToken();
        $user = $token?->getUser();

        // Fetch user entity
        if ($token !== null && $user !== null) {
            $identifier = $user->getUserIdentifier();
            $this->loginLoggerService->setUser($this->userRepository->findOneBy([
                'email' => $identifier
            ]));
        }

        $this->loginLoggerService->process(EnumLogLoginType::TYPE_FAILURE);
    }
}
