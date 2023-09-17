<?php

declare(strict_types=1);

namespace App\Announce\Domain\Entity;

use App\Announce\Domain\Entity\Traits\CityTrait;
use App\Announce\Domain\Entity\Traits\EntityIdTrait;
use App\Announce\Domain\Entity\Traits\EntityNameTrait;
use App\Announce\Domain\Entity\Traits\PropertyTrait;
use App\Announce\Domain\Repository\NeighborhoodRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: NeighborhoodRepository::class)]
#[UniqueEntity('slug')]
#[ORM\Table(name: 'announce_neighborhood')]
class Neighborhood
{
    use CityTrait;
    use EntityIdTrait;
    use EntityNameTrait;
    use PropertyTrait;

    final public const MAPPED_BY = 'neighborhood';
    final public const INVERSED_BY = 'neighborhoods';
    final public const GETTER = 'getNeighborhood';
    final public const SETTER = 'setNeighborhood';
}
