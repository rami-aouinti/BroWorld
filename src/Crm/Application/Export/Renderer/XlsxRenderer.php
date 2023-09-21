<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Export\Renderer;

use App\Crm\Application\Export\Base\XlsxRenderer as BaseXlsxRenderer;
use App\Crm\Application\Export\RendererInterface;

final class XlsxRenderer extends BaseXlsxRenderer implements RendererInterface
{
    public function getIcon(): string
    {
        return 'xlsx';
    }

    public function getTitle(): string
    {
        return 'xlsx';
    }
}
