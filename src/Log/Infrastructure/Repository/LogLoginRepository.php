<?php

declare(strict_types=1);

namespace App\Log\Infrastructure\Repository;

use App\General\Infrastructure\Repository\BaseRepository;
use App\Log\Domain\Entity\LogLogin as Entity;
use App\Log\Domain\Repository\Interfaces\LogLoginRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class LogLoginRepository
 *
 * @package App\Log
 *
 * @psalm-suppress LessSpecificImplementedReturnType
 * @codingStandardsIgnoreStart
 *
 * @method Entity|null find(string $id, ?int $lockMode = null, ?int $lockVersion = null, ?string $entityManagerName = null)
 * @method Entity|null findAdvanced(string $id, string | int | null $hydrationMode = null, string|null $entityManagerName = null)
 * @method Entity|null findOneBy(array $criteria, ?array $orderBy = null, ?string $entityManagerName = null)
 * @method Entity[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null, ?string $entityManagerName = null)
 * @method Entity[] findByAdvanced(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null, ?array $search = null, ?string $entityManagerName = null)
 * @method Entity[] findAll(?string $entityManagerName = null)
 *
 * @codingStandardsIgnoreEnd
 */
class LogLoginRepository extends BaseRepository implements LogLoginRepositoryInterface
{
    /**
     * @psalm-var class-string
     */
    protected static string $entityName = Entity::class;

    public function __construct(
        protected ManagerRegistry $managerRegistry,
        private int $databaseLogLoginHistoryDays,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function cleanHistory(): int
    {
        // Create query builder
        $queryBuilder = $this
            ->createQueryBuilder('ll')
            ->delete()
            ->where("ll.date < DATESUB(NOW(), :days, 'DAY')")
            ->setParameter('days', $this->databaseLogLoginHistoryDays);

        // Return deleted row count
        return (int)$queryBuilder->getQuery()->execute();
    }
}
