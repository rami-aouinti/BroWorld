<?php

declare(strict_types=1);

namespace App\User\Transport\EventSubscriber;

use App\Log\Application\Resource\LogLoginFailureResource;
use App\Log\Domain\Entity\LogLoginFailure;
use App\User\Application\Security\SecurityUser;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Throwable;

use function assert;
use function count;
use function is_string;

/**
 * Class LockedUserSubscriber
 *
 * @package App\User
 */
class LockedUserSubscriber implements EventSubscriberInterface
{
    /**
     * @param UserRepository $userRepository
     * @param LogLoginFailureResource $logLoginFailureResource
     * @param RequestStack $requestStack
     * @param int $lockUserOnLoginFailureAttempts
     */
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly LogLoginFailureResource $logLoginFailureResource,
        private readonly RequestStack $requestStack,
        private readonly int $lockUserOnLoginFailureAttempts,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationSuccessEvent::class => [
                'onAuthenticationSuccess',
                128,
            ],
            AuthenticationEvents::AUTHENTICATION_SUCCESS => [
                'onAuthenticationSuccess',
                128,
            ]
        ];
    }

    /**
     * @throws Throwable
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (
            $this->lockUserOnLoginFailureAttempts
            && count($user->getLogsLoginFailure()) > $this->lockUserOnLoginFailureAttempts
        ) {
            throw new LockedException('Locked account.');
        }

        $this->logLoginFailureResource->reset($user);
    }

    /**
     * @throws Throwable
     */
    public function onAuthenticationFailure(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request instanceof Request);
        $user = $this->getUser(
            (string)($request->query->get('email') ?? $request->request->get('email', ''))
        );

        if ($user !== null) {
            $this->logLoginFailureResource->save(new LogLoginFailure($user), true);
        }
    }

    /**
     * @throws Throwable
     */
    private function getUser(string | object $user): ?User
    {
        return match (true) {
            is_string($user) => $this->userRepository->findOneBy([
                'email' => $user->getEmail()
            ]),
            $user instanceof SecurityUser => $this->userRepository->findOneBy([
                'email' => $user->getUserIdentifier()]
            ),
            default => null,
        };
    }
}
