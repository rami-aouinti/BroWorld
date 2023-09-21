<?php

declare(strict_types=1);

namespace App\Announce\Transport\Controller;

use App\Announce\Domain\Repository\CityRepository;
use App\Announce\Domain\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SitemapController extends AbstractController
{
    private const DEFAULTS = ['_format' => 'xml'];

    #[Route(path: '/sitemap.xml', name: 'sitemap', options: ['sitemap' => true], defaults: self::DEFAULTS)]
    public function siteMap(): Response
    {
        return $this->render('announce/sitemap/sitemap.xml.twig', []);
    }

    #[Route(path: '/sitemap/cities.xml', name: 'cities_sitemap', options: ['sitemap' => true], defaults: self::DEFAULTS)]
    public function cities(CityRepository $cityRepository): Response
    {
        $cities = $cityRepository->findAll();

        return $this->render('announce/sitemap/cities.xml.twig', [
            'cities' => $cities,
        ]);
    }

    #[Route(path: '/sitemap/properties.xml', name: 'properties_sitemap', options: ['sitemap' => true], defaults: self::DEFAULTS)]
    public function properties(PropertyRepository $propertyRepository): Response
    {
        $properties = $propertyRepository->findAllPublished();

        return $this->render('announce/sitemap/properties.xml.twig', [
            'properties' => $properties,
        ]);
    }
}
