<?php

declare(strict_types=1);

namespace App\Log\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\User\Domain\Entity\User;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * Class LogLoginFailure
 *
 * @package App\Log
 */
#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'log_login_failure')]
#[ORM\Index(
    columns: ['user_id'],
    name: 'user_id',
)]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class LogLoginFailure implements EntityInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(
        name: 'timestamp',
        type: Types::DATETIME_IMMUTABLE,
    )]
    #[Groups([
        'LogLoginFailure',
        'LogLoginFailure.timestamp',
    ])]
    private DateTimeImmutable $timestamp;

    /**
     * Constructor
     *
     * @throws Throwable
     */
    public function __construct(
        #[ORM\ManyToOne(
            targetEntity: User::class,
            inversedBy: 'logsLoginFailure',
        )]
        #[ORM\JoinColumn(
            name: 'user_id',
            nullable: false,
            onDelete: 'CASCADE',
        )]
        #[Groups([
            'LogLoginFailure',
            'LogLoginFailure.user',
        ])]
        private User $user
    ) {
        $this->timestamp = new DateTimeImmutable(timezone: new DateTimeZone('UTC'));
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->getCreatedAt();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
