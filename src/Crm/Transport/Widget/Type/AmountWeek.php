<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Widget\Type;

use App\Crm\Transport\Widget\WidgetInterface;

final class AmountWeek extends AbstractAmountPeriod
{
    public function getOptions(array $options = []): array
    {
        return array_merge(['color' => WidgetInterface::COLOR_WEEK], parent::getOptions($options));
    }

    public function getId(): string
    {
        return 'AmountWeek';
    }

    public function getData(array $options = []): mixed
    {
        return $this->getRevenue('monday this week 00:00:00', 'sunday this week 23:59:59', $options);
    }

    public function getPermissions(): array
    {
        return ['view_all_data'];
    }
}