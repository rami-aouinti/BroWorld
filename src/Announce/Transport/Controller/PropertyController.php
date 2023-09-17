<?php

declare(strict_types=1);

namespace App\Announce\Transport\Controller;

use App\Announce\Domain\Entity\Property;
use App\Announce\Domain\Repository\FilterRepository;
use App\Announce\Domain\Repository\PropertyRepository;
use App\Announce\Domain\Repository\SimilarRepository;
use App\Announce\Application\Transformer\RequestToArrayTransformer;
use App\User\Application\Service\URLService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PropertyController extends BaseController
{
    #[Route(path: '/property', name: 'app_property', defaults: ['page' => 1], methods: ['GET'])]
    public function search(
        Request $request,
        FilterRepository $repository,
        RequestToArrayTransformer $transformer
    ): Response {
        $searchParams = $transformer->transform($request);
        $properties = $repository->findByFilter($searchParams);

        return $this->render(
            'property/index.html.twig',
            [
                'site' => $this->site($request),
                'properties' => $properties,
                'searchParams' => $searchParams,
            ]
        );
    }

    #[Route(path: '/map', name: 'map_view', methods: ['GET'])]
    public function mapView(Request $request, PropertyRepository $repository): Response
    {
        return $this->render(
            'property/map.html.twig',
            [
                'site' => $this->site($request),
                'properties' => $repository->findAllPublished(),
            ]
        );
    }

    #[Route(path: '/announce/{citySlug}/{slug}/{id<\d+>}', name: 'property_show', methods: ['GET'])]
    public function propertyShow(
        Request $request,
        URLService $url,
        Property $property,
        SimilarRepository $repository
    ): Response {
        if (!$url->isCanonical($property, $request)) {
            return $this->redirect($url->generateCanonical($property), 301);
        } elseif ($url->isRefererFromCurrentHost($request)) {
            $showBackButton = true;
        }
        return $this->render(
            'property/show.html.twig',
            [
                'site' => $this->site($request),
                'property' => $property,
                'properties' => $repository->findSimilarProperties($property),
                'number_of_photos' => \count($property->getPhotos()),
                'show_back_button' => $showBackButton ?? false,
            ]
        );
    }
}
