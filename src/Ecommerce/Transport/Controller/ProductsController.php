<?php

namespace App\Ecommerce\Transport\Controller;

use App\Ecommerce\Domain\Entity\Products;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ecommerce/produits', name: 'ecommerce_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index', options: ['sitemap' => true])]
    public function index(): Response
    {
        return $this->render('ecommerce/products/index.html.twig');
    }

    #[Route('/{slug}', name: 'details')]
    public function details(Products $product): Response
    {
        return $this->render('ecommerce/products/details.html.twig', compact('product'));
    }
}
