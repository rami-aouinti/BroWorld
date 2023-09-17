<?php

declare(strict_types=1);

namespace App\Announce\Infrastructure\DataFixtures;

use App\Announce\Domain\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getCategoryData() as [$slug, $name]) {
            $category = new Category();
            $category->setName($name);
            $category->setSlug($slug);
            $manager->persist($category);
            $this->addReference($name, $category);
        }
        $manager->flush();
    }

    private function getCategoryData(): array
    {
        return [
            // $categoryData = [$slug, $name];
            ['apartment', 'Apartment'],
            ['duplex', 'Duplex'],
            ['penthouse', 'Penthouse'],
            ['villa', 'Villa'],
        ];
    }
}
