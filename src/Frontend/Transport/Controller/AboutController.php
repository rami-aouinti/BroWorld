<?php

namespace App\Frontend\Transport\Controller;

use App\Frontend\Domain\Repository\AboutRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/about')]
class AboutController extends AbstractController
{
    #[Route('/', name: 'app_about', methods: ['GET'])]
    public function index(AboutRepository $aboutRepository): Response
    {
        return $this->render('about/index.html.twig', [
            'about' => $aboutRepository->findOneBy([
                'active' => true
            ]),
        ]);
    }
}
