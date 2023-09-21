<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Widget\Type;

use App\Crm\Domain\Repository\TimesheetRepository;
use App\Crm\Transport\Widget\WidgetInterface;

final class ActiveUsersYear extends AbstractCounterYear
{
    public function getOptions(array $options = []): array
    {
        return array_merge([
            'icon' => 'users',
            'color' => WidgetInterface::COLOR_YEAR,
        ], parent::getOptions($options));
    }

    public function getData(array $options = []): mixed
    {
        $this->setQuery(TimesheetRepository::STATS_QUERY_USER);
        $this->setQueryWithUser(false);

        return parent::getData($options);
    }

    protected function getFinancialYearTitle(): string
    {
        return 'stats.activeUsersFinancialYear';
    }

    public function getPermissions(): array
    {
        return ['ROLE_TEAMLEAD'];
    }

    public function getId(): string
    {
        return 'activeUsersYear';
    }
}
