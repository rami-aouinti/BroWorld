<?php

declare(strict_types=1);

namespace App\Announce\Domain\Entity;

use App\Announce\Domain\Entity\Traits\CityTrait;
use App\Announce\Domain\Entity\Traits\EntityIdTrait;
use App\Announce\Domain\Entity\Traits\EntityNameTrait;
use App\Announce\Domain\Entity\Traits\PropertyTrait;
use App\Announce\Domain\Repository\MetroRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: MetroRepository::class)]
#[UniqueEntity('slug')]
#[ORM\Table(name: 'announce_metro')]
class Metro
{
    use CityTrait;
    use EntityIdTrait;
    use EntityNameTrait;
    use PropertyTrait;

    final public const MAPPED_BY = 'metro_station';
    final public const INVERSED_BY = 'metro_stations';
    final public const GETTER = 'getMetroStation';
    final public const SETTER = 'setMetroStation';
}
