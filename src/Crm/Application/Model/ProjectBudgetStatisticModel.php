<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Model;

use App\Crm\Domain\Entity\Project;

/**
 * Object used to unify the access to budget data in charts.
 *
 * @internal do not use in plugins, no BC promise given!
 * @method Project getEntity()
 */
class ProjectBudgetStatisticModel extends BudgetStatisticModel
{
    public function __construct(Project $project)
    {
        parent::__construct($project);
    }

    public function getProject(): Project
    {
        return $this->getEntity();
    }
}
