<?php

namespace App\Frontend\Transport\Controller;

use App\Frontend\Domain\Repository\AboutRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/author')]
class AuthorController extends AbstractController
{
    #[Route('/', name: 'app_author', methods: ['GET'])]
    public function index(AboutRepository $aboutRepository): Response
    {
        return $this->render('frontend/author/index.html.twig');
    }
}
