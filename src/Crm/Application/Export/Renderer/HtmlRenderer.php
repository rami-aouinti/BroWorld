<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Export\Renderer;

use App\Crm\Application\Export\Base\HtmlRenderer as BaseHtmlRenderer;
use App\Crm\Application\Export\ExportRendererInterface;

final class HtmlRenderer extends BaseHtmlRenderer implements ExportRendererInterface
{
    public function getIcon(): string
    {
        return 'print';
    }

    public function getTitle(): string
    {
        return 'print';
    }
}
