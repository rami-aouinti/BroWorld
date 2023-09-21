<?php

namespace App\Ecommerce\Transport\Controller;

use App\Ecommerce\Domain\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/ecommerce', name: 'ecommerce_main', options: ['sitemap' => true])]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        return $this->render('ecommerce/main/index.html.twig', [
            'categories' => $categoriesRepository->findBy([], ['categoryOrder' => 'asc'])
        ]);
    }
}
