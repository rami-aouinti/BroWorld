<?php

namespace App\Resume\Controller;

use App\Form\HobbyType;
use App\Resume\Model\Entity\Hobby;
use App\Resume\Model\Repository\HobbyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/hobby')]
class HobbyController extends AbstractController
{
    #[Route('/', name: 'app_hobby_index', methods: ['GET'])]
    public function index(HobbyRepository $hobbyRepository): Response
    {
        return $this->render('hobby/index.html.twig', [
            'hobbies' => $hobbyRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_hobby_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hobby = new Hobby();
        $form = $this->createForm(HobbyType::class, $hobby);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hobby);
            $entityManager->flush();

            return $this->redirectToRoute('app_hobby_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hobby/new.html.twig', [
            'hobby' => $hobby,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hobby_show', methods: ['GET'])]
    public function show(Hobby $hobby): Response
    {
        return $this->render('hobby/show.html.twig', [
            'hobby' => $hobby,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hobby_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hobby $hobby, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HobbyType::class, $hobby);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_hobby_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hobby/edit.html.twig', [
            'hobby' => $hobby,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hobby_delete', methods: ['POST'])]
    public function delete(Request $request, Hobby $hobby, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hobby->getId(), $request->request->get('_token'))) {
            $entityManager->remove($hobby);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hobby_index', [], Response::HTTP_SEE_OTHER);
    }
}
