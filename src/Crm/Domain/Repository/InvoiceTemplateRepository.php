<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Domain\Repository;

use App\Crm\Application\Utils\Pagination;
use App\Crm\Domain\Entity\InvoiceTemplate;
use App\Crm\Domain\Repository\Paginator\PaginatorInterface;
use App\Crm\Domain\Repository\Paginator\QueryBuilderPaginator;
use App\Crm\Domain\Repository\Query\BaseQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends \Doctrine\ORM\EntityRepository<InvoiceTemplate>
 */
class InvoiceTemplateRepository extends EntityRepository
{
    public function hasTemplate(): bool
    {
        return $this->count([]) > 0;
    }

    public function getQueryBuilderForFormType(): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('t')
            ->from(InvoiceTemplate::class, 't')
            ->orderBy('t.name');

        return $qb;
    }

    private function getQueryBuilderForQuery(BaseQuery $query): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('t')
            ->from(InvoiceTemplate::class, 't')
            ->orderBy('t.name');

        return $qb;
    }

    protected function getPaginatorForQuery(BaseQuery $query): PaginatorInterface
    {
        $counter = $this->countTemplatesForQuery($query);
        $qb = $this->getQueryBuilderForQuery($query);

        return new QueryBuilderPaginator($qb, $counter);
    }

    public function getPagerfantaForQuery(BaseQuery $query): Pagination
    {
        return new Pagination($this->getPaginatorForQuery($query), $query);
    }

    public function countTemplatesForQuery(BaseQuery $query): int
    {
        $qb = $this->getQueryBuilderForQuery($query);
        $qb
            ->resetDQLPart('select')
            ->resetDQLPart('orderBy')
            ->select($qb->expr()->countDistinct('t.id'))
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function saveTemplate(InvoiceTemplate $template): void
    {
        $this->getEntityManager()->persist($template);
        $this->getEntityManager()->flush();
    }

    public function removeTemplate(InvoiceTemplate $template): void
    {
        $this->getEntityManager()->remove($template);
        $this->getEntityManager()->flush();
    }
}
