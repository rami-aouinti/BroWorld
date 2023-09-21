<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Widget\Type;

use App\Crm\Transport\Event\UserRevenueStatisticEvent;
use App\Crm\Application\Configuration\SystemConfiguration;
use App\Crm\Application\Model\Revenue;
use App\Crm\Domain\Repository\TimesheetRepository;
use App\Crm\Transport\Widget\WidgetInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class UserAmountYear extends AbstractCounterYear
{
    public function __construct(TimesheetRepository $repository, SystemConfiguration $systemConfiguration, private EventDispatcherInterface $dispatcher)
    {
        parent::__construct($repository, $systemConfiguration);
    }

    public function getTemplateName(): string
    {
        return 'widget/widget-counter-money.html.twig';
    }

    public function getPermissions(): array
    {
        return ['view_rate_own_timesheet'];
    }

    protected function getFinancialYearTitle(): string
    {
        return 'stats.amountFinancialYear';
    }

    public function getId(): string
    {
        return 'UserAmountYear';
    }

    public function getOptions(array $options = []): array
    {
        return array_merge([
            'icon' => 'money',
            'color' => WidgetInterface::COLOR_YEAR,
        ], parent::getOptions($options));
    }

    public function getData(array $options = []): mixed
    {
        $this->setQuery(TimesheetRepository::STATS_QUERY_RATE);
        $this->setQueryWithUser(true);

        /** @var array<Revenue> $data */
        $data = parent::getData($options);

        $event = new UserRevenueStatisticEvent($this->getUser(), $this->getBegin(), $this->getEnd());
        foreach ($data as $row) {
            $event->addRevenue($row->getCurrency(), $row->getAmount());
        }
        $this->dispatcher->dispatch($event);

        return $event->getRevenue();
    }
}
