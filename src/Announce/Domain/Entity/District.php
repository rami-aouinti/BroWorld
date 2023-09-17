<?php

declare(strict_types=1);

namespace App\Announce\Domain\Entity;

use App\Announce\Domain\Entity\Traits\CityTrait;
use App\Announce\Domain\Entity\Traits\EntityIdTrait;
use App\Announce\Domain\Entity\Traits\EntityNameTrait;
use App\Announce\Domain\Entity\Traits\PropertyTrait;
use App\Announce\Domain\Repository\DistrictRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: DistrictRepository::class)]
#[UniqueEntity('slug')]
#[ORM\Table(name: 'announce_district')]
class District
{
    use CityTrait;
    use EntityIdTrait;
    use EntityNameTrait;
    use PropertyTrait;

    final public const MAPPED_BY = 'district';
    final public const INVERSED_BY = 'districts';
    final public const GETTER = 'getDistrict';
    final public const SETTER = 'setDistrict';
}
