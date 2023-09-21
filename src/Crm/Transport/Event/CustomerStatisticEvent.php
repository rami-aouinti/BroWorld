<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Event;

use App\Crm\Application\Model\CustomerStatistic;
use App\Crm\Domain\Entity\Customer;

final class CustomerStatisticEvent extends AbstractCustomerEvent
{
    public function __construct(Customer $customer, private CustomerStatistic $statistic, private ?\DateTime $begin = null, private ?\DateTime $end = null)
    {
        parent::__construct($customer);
    }

    public function getStatistic(): CustomerStatistic
    {
        return $this->statistic;
    }

    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }
}
