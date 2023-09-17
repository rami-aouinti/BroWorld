<?php

declare(strict_types=1);

namespace App\User\Application\Security;

use App\User\Transport\Service\AuthenticatorServiceInterface;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Class UserTypeIdentification
 *
 * @package App\User
 */
class UserTypeIdentification implements AuthenticatorServiceInterface
{
    /**
     * @param UserRepository $userRepository
     * @param Security $security
     */
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Security $security
    ) {
    }


    /**
     * Helper method to get current logged in User entity via token storage.
     *
     * @throws NonUniqueResultException
     */
    public function getUser(): ?User
    {
        $user = $this->security->getUser();
        return $user === null ? null : $this->userRepository->findOneBy([
            'email' => $user->getUserIdentifier()
        ]);
    }

    public function getAuthUser(): ?User
    {
        $user = $this->getSecurityUser();
        return $user === null ? null : $this->userRepository->findOneBy([
            'email' => $user->getEmail()
        ]);
    }

    /**
     * Helper method to get user identity object via token storage.
     */
    public function getIdentity(): ?UserInterface
    {
        return $this->getSecurityUser();
    }

    /**
     * Helper method to get current logged in SecurityUser via token storage.
     */
    public function getSecurityUser(): ?User
    {
        $securityUser = $this->getUser();
        return $securityUser instanceof User ? $securityUser : null;
    }
}
