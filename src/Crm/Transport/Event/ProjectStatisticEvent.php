<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Event;

use App\Crm\Application\Model\ProjectStatistic;
use App\Crm\Domain\Entity\Project;

final class ProjectStatisticEvent extends AbstractProjectEvent
{
    public function __construct(Project $project, private ProjectStatistic $statistic, private ?\DateTime $begin = null, private ?\DateTime $end = null)
    {
        parent::__construct($project);
    }

    public function getStatistic(): ProjectStatistic
    {
        return $this->statistic;
    }

    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }
}
