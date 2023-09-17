<?php

declare(strict_types=1);

namespace App\Announce\Infrastructure\DataFixtures;

use App\User\Domain\Entity\Profile;
use App\User\Domain\Entity\User;
use App\Announce\Application\Transformer\UserTransformer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class UserFixtures extends Fixture
{
    public function __construct(private readonly UserTransformer $transformer)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$fullName, $username, $phone, $email, $roles]) {
            $user = new User();
            $user->setPassword($username);
            $user->setEmail($email);
            $user->setRoles($roles);
            $user->setProfile(
                (new Profile())
                    ->setFullName($fullName)
            );
            $user->setEmailVerifiedAt(new \DateTime('now'));
            $user = $this->transformer->transform($user);
            $manager->persist($user);
            $this->addReference($username, $user);
        }
        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
            ['John Smith', 'admin', '0(0)99766899', 'admin@admin.com', ['ROLE_ADMIN', 'ROLE_USER']],
            ['Rhonda Jordan', 'user', '0(0)99766899', 'user@user.com', ['ROLE_USER']],
        ];
    }
}
