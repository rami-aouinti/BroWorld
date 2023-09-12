<?php

namespace App\Frontend\Model\Entity;

use App\Frontend\Model\Repository\SettingRepository;
use App\User\Model\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $sidebar = null;

    #[ORM\Column(length: 255)]
    private ?string $sidenav = null;

    #[ORM\Column(length: 255)]
    private ?string $navbar = null;

    #[ORM\OneToOne(inversedBy: 'setting', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSidebar(): ?string
    {
        return $this->sidebar;
    }

    public function setSidebar(string $sidebar): static
    {
        $this->sidebar = $sidebar;

        return $this;
    }

    public function getSidenav(): ?string
    {
        return $this->sidenav;
    }

    public function setSidenav(string $sidenav): static
    {
        $this->sidenav = $sidenav;

        return $this;
    }

    public function getNavbar(): ?string
    {
        return $this->navbar;
    }

    public function setNavbar(string $navbar): static
    {
        $this->navbar = $navbar;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
