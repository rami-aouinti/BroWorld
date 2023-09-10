<?php

namespace App\Controller;

use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/news')]
class NewsController extends AbstractController
{
    #[Route('/', name: 'app_news_index', methods: ['GET'])]
    public function index(NewsRepository $newsRepository): Response
    {
        return $this->render('news/index.html.twig', [
            'news' => $newsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_news_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($news);
            $entityManager->flush();

            return $this->redirectToRoute('app_news_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('news/new.html.twig', [
            'news' => $news,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_news_show', methods: ['GET'])]
    public function show(News $news): Response
    {
        return $this->render('news/show.html.twig', [
            'news' => $news,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_news_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_news_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('news/edit.html.twig', [
            'news' => $news,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_news_delete', methods: ['POST'])]
    public function delete(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$news->getId(), $request->request->get('_token'))) {
            $entityManager->remove($news);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_news_index', [], Response::HTTP_SEE_OTHER);
    }
}
