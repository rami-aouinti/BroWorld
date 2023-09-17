<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Entity\Traits\EntityIdTrait;
use App\User\Domain\Repository\ProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
class Profile
{
    use EntityIdTrait;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $full_name;

    #[ORM\OneToOne(inversedBy: 'profile', targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function setFullName(?string $full_name): self
    {
        $this->full_name = $full_name;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
