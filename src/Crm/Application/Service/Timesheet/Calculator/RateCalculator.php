<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Service\Timesheet\Calculator;

use App\Crm\Application\Service\Timesheet\CalculatorInterface;
use App\Crm\Application\Service\Timesheet\RateService;
use App\Crm\Domain\Entity\Timesheet;

/**
 * Implementation to calculate the rate for a timesheet record.
 */
final class RateCalculator implements CalculatorInterface
{
    public function __construct(private RateService $service)
    {
    }

    public function calculate(Timesheet $record, array $changeset): void
    {
        $rate = $this->service->calculate($record);

        $record->setRate($rate->getRate());
        $record->setInternalRate($rate->getInternalRate());

        if ($rate->getHourlyRate() !== null) {
            $record->setHourlyRate($rate->getHourlyRate());
        }

        if ($rate->getFixedRate() !== null) {
            $record->setFixedRate($rate->getFixedRate());
        }
    }

    public function getPriority(): int
    {
        return 300;
    }
}
