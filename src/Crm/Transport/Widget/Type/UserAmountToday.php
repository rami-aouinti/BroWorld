<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Widget\Type;

use App\Crm\Transport\Widget\WidgetInterface;

final class UserAmountToday extends AbstractUserRevenuePeriod
{
    public function getOptions(array $options = []): array
    {
        return array_merge(['color' => WidgetInterface::COLOR_TODAY], parent::getOptions($options));
    }

    public function getId(): string
    {
        return 'UserAmountToday';
    }

    public function getData(array $options = []): mixed
    {
        return $this->getRevenue('00:00:00', '23:59:59', $options);
    }
}
