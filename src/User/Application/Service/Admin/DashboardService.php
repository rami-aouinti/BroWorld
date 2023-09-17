<?php

declare(strict_types=1);

namespace App\User\Application\Service\Admin;

use App\Announce\Domain\Entity\Category;
use App\Announce\Domain\Entity\City;
use App\Announce\Domain\Entity\DealType;
use App\Frontend\Domain\Entity\Page;
use App\Announce\Domain\Entity\Property;
use App\User\Domain\Entity\User;
use App\User\Application\Service\Cache\GetCache;

final class DashboardService
{
    use GetCache;

    public function countProperties(): int
    {
        return $this->getCount('properties_count', Property::class);
    }

    public function countCities(): int
    {
        return $this->getCount('cities_count', City::class);
    }

    public function countDealTypes(): int
    {
        return $this->getCount('deal_types_count', DealType::class);
    }

    public function countCategories(): int
    {
        return $this->getCount('categories_count', Category::class);
    }

    public function countPages(): int
    {
        return $this->getCount('pages_count', Page::class);
    }

    public function countUsers(): int
    {
        return $this->getCount('users_count', User::class);
    }
}
