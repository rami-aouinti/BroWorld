<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Controller;

use App\Crm\Transport\Event\TimesheetMetaDisplayEvent;
use App\Crm\Transport\Form\TimesheetEditForm;
use App\Crm\Application\Export\ServiceExport;
use App\Crm\Domain\Entity\Timesheet;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/timesheet')]
#[IsGranted('view_own_timesheet')]
final class TimesheetController extends TimesheetAbstractController
{
    #[Route(path: '/', defaults: ['page' => 1], name: 'timesheet', methods: ['GET'])]
    #[Route(path: '/page/{page}', requirements: ['page' => '[1-9]\d*'], name: 'timesheet_paginated', methods: ['GET'])]
    #[IsGranted('view_own_timesheet')]
    public function indexAction(int $page, Request $request): Response
    {
        $query = $this->createDefaultQuery();
        $query->setPage($page);

        return $this->index($query, $request, 'timesheet', 'timesheet_paginated', TimesheetMetaDisplayEvent::TIMESHEET);
    }

    #[Route(path: '/export/', name: 'timesheet_export', methods: ['GET', 'POST'])]
    #[IsGranted('export_own_timesheet')]
    public function exportAction(Request $request, ServiceExport $serviceExport): Response
    {
        return $this->export($request, $serviceExport);
    }

    #[Route(path: '/{id}/edit', name: 'timesheet_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'entry')]
    public function editAction(Timesheet $entry, Request $request): Response
    {
        return $this->edit($entry, $request);
    }

    #[Route(path: '/{id}/duplicate', name: 'timesheet_duplicate', methods: ['GET', 'POST'])]
    #[IsGranted('duplicate', 'entry')]
    public function duplicateAction(Timesheet $entry, Request $request): Response
    {
        return $this->duplicate($entry, $request);
    }

    #[Route(path: '/multi-update', name: 'timesheet_multi_update', methods: ['POST'])]
    #[IsGranted('edit_own_timesheet')]
    public function multiUpdateAction(Request $request): Response
    {
        return $this->multiUpdate($request);
    }

    #[Route(path: '/multi-delete', name: 'timesheet_multi_delete', methods: ['POST'])]
    #[IsGranted('delete_own_timesheet')]
    public function multiDeleteAction(Request $request): Response
    {
        return $this->multiDelete($request);
    }

    #[Route(path: '/create', name: 'timesheet_create', methods: ['GET', 'POST'])]
    #[IsGranted('create_own_timesheet')]
    public function createAction(Request $request): Response
    {
        return $this->create($request);
    }

    protected function getCreateForm(Timesheet $entry): FormInterface
    {
        return $this->generateCreateForm($entry, TimesheetEditForm::class, $this->generateUrl('timesheet_create'));
    }

    protected function getDuplicateForm(Timesheet $entry, Timesheet $original): FormInterface
    {
        return $this->generateCreateForm($entry, TimesheetEditForm::class, $this->generateUrl('timesheet_duplicate', ['id' => $original->getId()]));
    }
}
