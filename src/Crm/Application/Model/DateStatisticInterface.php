<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Model;

use App\Crm\Application\Model\Statistic\StatisticDate;
use DateTimeInterface;

interface DateStatisticInterface
{
    /**
     * For unified frontend access
     *
     * @return StatisticDate[]
     */
    public function getData(): array;

    public function getByDateTime(DateTimeInterface $date): ?StatisticDate;

    /**
     * @return DateTimeInterface[]
     */
    public function getDateTimes(): array;
}
