<?php

namespace App\Admin\Transport\Controller\Ecommerce;

use App\Ecommerce\Domain\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/ecommerce/categories', name: 'ecommerce_admin_categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findBy([], ['categoryOrder' => 'asc']);

        return $this->render('ecommerce/admin/categories/index.html.twig', compact('categories'));
    }
}
