<?php

declare(strict_types=1);

namespace App\User\Transport\Controller\Admin\Announce;

use App\Announce\Transport\Controller\BaseController;
use App\User\Application\Service\Admin\DashboardService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends BaseController
{
    #[Route(path: '/admin/announcement', name: 'admin_announce_dashboard')]
    public function index(Request $request, DashboardService $service): Response
    {
        $properties = $service->countProperties();

        $cities = $service->countCities();

        $dealTypes = $service->countDealTypes();

        $categories = $service->countCategories();

        $pages = $service->countPages();

        $users = $service->countUsers();

        return $this->render('admin/dashboard/index.html.twig', [
            'site' => $this->site($request),
            'number_of_properties' => $properties,
            'number_of_cities' => $cities,
            'number_of_deal_types' => $dealTypes,
            'number_of_categories' => $categories,
            'number_of_pages' => $pages,
            'number_of_users' => $users,
        ]);
    }
}
