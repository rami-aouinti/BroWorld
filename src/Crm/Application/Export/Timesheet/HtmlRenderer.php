<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Export\Timesheet;

use App\Crm\Application\Export\Base\HtmlRenderer as BaseHtmlRenderer;
use App\Crm\Application\Export\TimesheetExportInterface;

final class HtmlRenderer extends BaseHtmlRenderer implements TimesheetExportInterface
{
    protected function getTemplate(): string
    {
        return 'timesheet/export.html.twig';
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'print';
    }
}
