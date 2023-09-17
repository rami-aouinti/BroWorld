<?php

declare(strict_types=1);

namespace App\Announce\Transport\Controller;

use App\Frontend\Transport\Controller\Traits\MenuTrait;
use App\Announce\Domain\Entity\Category;
use App\Announce\Domain\Entity\City;
use App\Announce\Domain\Entity\DealType;
use App\Announce\Domain\Entity\Feature;
use App\Frontend\Domain\Repository\SettingsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseController extends AbstractController
{
    use MenuTrait;

    public function __construct(
        private readonly SettingsRepository $settingsRepository,
        protected ManagerRegistry $doctrine
    ) {
    }

    private function searchFields(): array
    {
        // Get city
        $cities = $this->doctrine
            ->getRepository(City::class)->findAll();

        // Get categories
        $categories = $this->doctrine
            ->getRepository(Category::class)->findAll();

        // Get deal types
        $dealTypes = $this->doctrine
            ->getRepository(DealType::class)->findAll();

        // Get features
        $features = $this->doctrine
            ->getRepository(Feature::class)->findAll();

        return [
            'cities' => $cities,
            'features' => $features,
            'categories' => $categories,
            'deal_types' => $dealTypes,
        ];
    }

    public function site(Request $request): array
    {
        $settings = $this->settingsRepository->findAllAsArray();

        $fields = $this->searchFields();

        $this->setDoctrine($this->doctrine);

        $menu = $this->menu($request);

        return array_merge($settings, $fields, $menu);
    }
}
