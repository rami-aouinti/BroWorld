<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\User;

use App\Crm\Transport\Event\UserInteractiveLoginEvent;
use App\Crm\Application\Security\UserChecker;
use App\User\Domain\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class LoginManager
{
    public function __construct(
        private readonly TokenStorageInterface                  $tokenStorage,
        private readonly UserChecker                            $userChecker,
        private readonly SessionAuthenticationStrategyInterface $sessionStrategy,
        private readonly RequestStack                           $requestStack,
        private readonly EventDispatcherInterface               $eventDispatcher,
    ) {
    }

    /**
     * @param User $user
     * @param Response|null $response
     * @return void
     */
    public function logInUser(User $user, Response $response = null): void
    {
        $this->userChecker->checkPreAuth($user);

        $token = $this->createToken($user);
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request) {
            $this->sessionStrategy->onAuthentication($request, $token);
        }

        $this->tokenStorage->setToken($token);

        $this->eventDispatcher->dispatch(new UserInteractiveLoginEvent($user));
    }

    private function createToken(User $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, 'secured_area', $user->getRoles());
    }
}
