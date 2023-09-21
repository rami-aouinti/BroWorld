<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/widgets')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
final class WidgetController extends AbstractController
{
    #[Route(path: '/working-time/{year}/{week}', name: 'widgets_working_time_chart', requirements: ['year' => '[1-9]\d*', 'week' => '[0-9]\d*'], methods: ['GET'])]
    #[IsGranted('view_own_timesheet')]
    public function workingtimechartAction($year, $week): Response
    {
        return $this->render('widget/paginatedworkingtimechart.html.twig', [
            'user' => $this->getUser(),
            'year' => $year,
            'week' => $week,
        ]);
    }
}
