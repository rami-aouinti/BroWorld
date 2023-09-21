<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Controller\Reporting;

use App\Crm\Application\Service\Project\ProjectStatisticService;
use App\Crm\Application\Service\Reporting\ProjectInactive\ProjectInactiveForm;
use App\Crm\Application\Service\Reporting\ProjectInactive\ProjectInactiveQuery;
use App\Crm\Transport\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProjectInactiveController extends AbstractController
{
    #[Route(path: '/reporting/project_inactive', name: 'report_project_inactive', methods: ['GET', 'POST'])]
    #[IsGranted('report:project')]
    #[IsGranted(new Expression("is_granted('budget_any', 'project')"))]
    public function __invoke(Request $request, ProjectStatisticService $service): Response
    {
        $dateFactory = $this->getDateTimeFactory();
        $user = $this->getUser();
        $now = $dateFactory->createDateTime();

        $query = new ProjectInactiveQuery($dateFactory->createDateTime('-1 year'), $user);
        $form = $this->createFormForGetRequest(ProjectInactiveForm::class, $query, [
            'timezone' => $user->getTimezone()
        ]);
        $form->submit($request->query->all(), false);

        $projects = $service->findInactiveProjects($query);
        $entries = $service->getProjectView($user, $projects, $now);

        $byCustomer = [];
        foreach ($entries as $entry) {
            $customer = $entry->getProject()->getCustomer();
            if (!isset($byCustomer[$customer->getId()])) {
                $byCustomer[$customer->getId()] = ['customer' => $customer, 'projects' => []];
            }
            $byCustomer[$customer->getId()]['projects'][] = $entry;
        }

        return $this->render('reporting/project_inactive.html.twig', [
            'entries' => $byCustomer,
            'form' => $form->createView(),
            'report_title' => 'report_inactive_project',
            'tableName' => 'inactive_project_reporting',
            'now' => $now,
            'skipColumns' => ['today', 'week', 'month', 'projectStart', 'projectEnd', 'comment'],
        ]);
    }
}
