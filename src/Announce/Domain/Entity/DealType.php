<?php

declare(strict_types=1);

namespace App\Announce\Domain\Entity;

use App\Announce\Domain\Entity\Traits\EntityIdTrait;
use App\Announce\Domain\Entity\Traits\EntityNameTrait;
use App\Announce\Domain\Entity\Traits\PropertyTrait;
use App\Announce\Domain\Repository\DealTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: DealTypeRepository::class)]
#[UniqueEntity('slug')]
#[ORM\Table(name: 'announce_deal_type')]
class DealType
{
    use EntityIdTrait;
    use EntityNameTrait;
    use PropertyTrait;

    final public const MAPPED_BY = 'deal_type';
}
