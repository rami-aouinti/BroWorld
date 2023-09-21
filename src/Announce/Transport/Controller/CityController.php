<?php

declare(strict_types=1);

namespace App\Announce\Transport\Controller;

use App\Announce\Domain\Entity\City;
use App\User\Application\Service\CityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CityController extends BaseController
{
    #[Route(path: '/city/{slug}', name: 'city', defaults: ['page' => 1], methods: ['GET'])]
    public function index(Request $request, City $city, CityService $service): Response
    {
        $searchParams = $service->getSearchParams($request, $city);
        $properties = $service->getProperties($searchParams);
        $siteOptions = $service->decorateOptions($this->site($request), $city);

        return $this->render('announce/property/index.html.twig',
            [
                'site' => $siteOptions,
                'properties' => $properties,
                'searchParams' => $searchParams,
            ]
        );
    }
}
