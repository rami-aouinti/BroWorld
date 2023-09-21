<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Event;

use App\Crm\Domain\Entity\Timesheet;

final class TimesheetDuplicatePostEvent extends AbstractTimesheetEvent
{
    public function __construct(Timesheet $new, private Timesheet $original)
    {
        parent::__construct($new);
    }

    public function getOriginalTimesheet(): Timesheet
    {
        return $this->original;
    }
}
