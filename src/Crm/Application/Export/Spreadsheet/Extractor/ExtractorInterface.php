<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Export\Spreadsheet\Extractor;

use App\Crm\Application\Export\Spreadsheet\ColumnDefinition;

/**
 * Extract ColumnDefinition objects from various sources.
 */
interface ExtractorInterface
{
    /**
     * @param mixed $value
     * @return ColumnDefinition[]
     * @throws ExtractorException
     */
    public function extract($value): array;
}
