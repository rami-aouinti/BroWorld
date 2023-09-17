<?php

declare(strict_types=1);

namespace App\Frontend\Domain\Entity;

use App\User\Domain\Entity\Traits\EntityIdTrait;
use App\Frontend\Domain\Repository\SettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
#[UniqueEntity('setting_name')]
#[ORM\Table(name: 'frontend_settings')]
class Settings
{
    use EntityIdTrait;

    #[ORM\Column(type: Types::STRING, length: 191, unique: true)]
    private ?string $setting_name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $setting_value;

    public function getSettingName(): ?string
    {
        return $this->setting_name;
    }

    public function setSettingName(string $setting_name): self
    {
        $this->setting_name = $setting_name;

        return $this;
    }

    public function getSettingValue(): ?string
    {
        return $this->setting_value;
    }

    public function setSettingValue(?string $setting_value): self
    {
        $this->setting_value = $setting_value;

        return $this;
    }
}
