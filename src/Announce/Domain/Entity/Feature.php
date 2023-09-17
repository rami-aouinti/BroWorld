<?php

declare(strict_types=1);

namespace App\Announce\Domain\Entity;

use App\Announce\Domain\Entity\Traits\EntityIdTrait;
use App\Announce\Domain\Repository\FeatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeatureRepository::class)]
#[ORM\Table(name: 'announce_feature')]
class Feature
{
    use EntityIdTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name;

    #[ORM\ManyToMany(targetEntity: Property::class, mappedBy: 'features')]
    private $properties;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $icon;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function addProperty(Property $property): self
    {
        if (!$this->properties->contains($property)) {
            $this->properties[] = $property;
            $property->addFeature($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): self
    {
        if ($this->properties->contains($property)) {
            $this->properties->removeElement($property);
            $property->removeFeature($this);
        }

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }
}
