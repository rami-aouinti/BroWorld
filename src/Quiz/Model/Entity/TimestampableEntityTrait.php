<?php

declare(strict_types=1);

namespace App\Quiz\Model\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Project specific Timestampable behavior trait.
 *
 * @see https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/timestampable.md#traits
 * @see TimestampableEntity
 */
trait TimestampableEntityTrait
{
    /**
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(type="datetime", name="created_at")
     */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: Types::DATE_MUTABLE, nullable: true)]
    protected ?\DateTime $createdAt = null;

    /**
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime", name="updated_at")
     */
    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated_at', type: Types::DATE_MUTABLE, nullable: true)]
    protected ?\DateTime $updatedAt = null;

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
