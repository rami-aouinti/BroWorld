<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\User;

use App\Crm\Application\Configuration\SystemConfiguration;
use App\User\Domain\Entity\User;
use App\Crm\Domain\Entity\UserPreference;
use App\Crm\Transport\Event\UserCreateEvent;
use App\Crm\Transport\Event\UserCreatePostEvent;
use App\Crm\Transport\Event\UserCreatePreEvent;
use App\Crm\Transport\Event\UserDeletePostEvent;
use App\Crm\Transport\Event\UserDeletePreEvent;
use App\Crm\Transport\Event\UserUpdatePostEvent;
use App\Crm\Transport\Event\UserUpdatePreEvent;
use App\User\Domain\Repository\UserRepository;
use App\Crm\Transport\Validator\ValidationFailedException;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @final
 */
class UserService
{
    /**
     * @var array<string, int>
     */
    private array $cache = [];

    public function __construct(
        private UserRepository $repository,
        private EventDispatcherInterface $dispatcher,
        private ValidatorInterface $validator,
        private SystemConfiguration $configuration,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function countUser(?bool $enabled = null): int
    {
        $key = 'count' . ($enabled === null ? '_all' : ($enabled ? '_visible' : '_invisible'));

        if (!\array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $this->repository->countUser($enabled);
        }

        return $this->cache[$key];
    }

    public function createNewUser(): User
    {
        $user = new User();
        $user->setEnabled(true);
        $user->setTimezone($this->configuration->getUserDefaultTimezone());
        $user->setLanguage($this->configuration->getUserDefaultLanguage());
        $user->setPreferenceValue(UserPreference::SKIN, $this->configuration->getUserDefaultTheme());

        // Attention: PrepareUserEvent cannot be dispatched on console, as it calls isGranted()
        $this->dispatcher->dispatch(new UserCreateEvent($user));

        return $user;
    }

    public function saveNewUser(User $user): User
    {
        if (null !== $user->getId()) {
            throw new InvalidArgumentException('Cannot create user, already persisted');
        }

        $this->validateUser($user, ['Registration', 'UserCreate']);

        $this->hashPassword($user);
        $this->hashApiToken($user);

        $this->dispatcher->dispatch(new UserCreatePreEvent($user)); // @CloudRequired
        $this->repository->saveUser($user);
        $this->dispatcher->dispatch(new UserCreatePostEvent($user));

        return $user;
    }

    /**
     * @param User $user
     * @param string[] $groups
     * @throws ValidationFailedException
     */
    private function validateUser(User $user, array $groups = []): void
    {
        $errors = $this->validator->validate($user, null, $groups);

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors, 'Validation Failed');
        }
    }

    public function updateUser(User $user, array $groups = []): User
    {
        $this->validateUser($user, $groups);

        $this->hashPassword($user);
        $this->hashApiToken($user);

        $this->dispatcher->dispatch(new UserUpdatePreEvent($user));
        $this->repository->saveUser($user);
        $this->dispatcher->dispatch(new UserUpdatePostEvent($user));

        return $user;
    }

    public function findUserByUsernameOrThrowException(string $username): User
    {
        $user = $this->findUserByName($username);

        if ($user === null) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
        }

        return $user;
    }

    public function findUserByUsernameOrEmail(string $usernameOrEmail): ?User
    {
        return $this->repository->loadUserByIdentifier($usernameOrEmail);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function findUserByName(string $name): ?User
    {
        return $this->repository->findByUsername($name);
    }

    public function findUserByConfirmationToken(string $token): ?User
    {
        return $this->repository->findOneBy(['confirmationToken' => $token]);
    }

    public function generateSecurityToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function hashPassword(User $user): void
    {
        $plain = $user->getPlainPassword();

        if ($plain === null || 0 === \strlen($plain)) {
            return;
        }

        $password = $this->passwordHasher->hashPassword($user, $plain);
        $user->setPassword($password);
        $user->eraseCredentials();
    }

    private function hashApiToken(User $user): void
    {
        $plain = $user->getPlainApiToken();

        if ($plain === null || 0 === \strlen($plain)) {
            return;
        }

        $password = $this->passwordHasher->hashPassword($user, $plain);
        $user->setApiToken($password);
        $user->eraseCredentials();
    }

    public function deleteUser(User $delete, ?User $replace = null): void
    {
        $this->dispatcher->dispatch(new UserDeletePreEvent($delete, $replace));
        $this->repository->deleteUser($delete, $replace);
        $this->dispatcher->dispatch(new UserDeletePostEvent($delete, $replace));
    }
}
