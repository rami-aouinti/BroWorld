<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Export\Timesheet;

use App\Crm\Application\Export\Base\CsvRenderer as BaseCsvRenderer;
use App\Crm\Application\Export\TimesheetExportInterface;

final class CsvRenderer extends BaseCsvRenderer implements TimesheetExportInterface
{
}
