<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Event;

use App\Crm\Application\Model\PermissionSectionInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event can be used, to dynamically add sections to the permission screen.
 */
final class PermissionSectionsEvent extends Event
{
    /**
     * @var array<PermissionSectionInterface>
     */
    private array $sections = [];

    public function addSection(PermissionSectionInterface $section): PermissionSectionsEvent
    {
        $this->sections[] = $section;

        return $this;
    }

    /**
     * @return PermissionSectionInterface[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }
}
