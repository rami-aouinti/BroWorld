<?php

namespace App\Resume\Transport\Controller;

use App\Resume\Domain\Entity\Education;
use App\Resume\Domain\Repository\EducationRepository;
use App\Resume\Transport\Form\EducationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/education')]
class EducationController extends AbstractController
{
    #[Route('/', name: 'app_education_index', methods: ['GET'])]
    public function index(EducationRepository $educationRepository): Response
    {
        return $this->render('education/index.html.twig', [
            'education' => $educationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_education_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $education = new Education();
        $form = $this->createForm(EducationType::class, $education);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($education);
            $entityManager->flush();

            return $this->redirectToRoute('app_education_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('education/new.html.twig', [
            'education' => $education,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_education_show', methods: ['GET'])]
    public function show(Education $education): Response
    {
        return $this->render('education/show.html.twig', [
            'education' => $education,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_education_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Education $education, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EducationType::class, $education);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_education_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('education/edit.html.twig', [
            'education' => $education,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_education_delete', methods: ['POST'])]
    public function delete(Request $request, Education $education, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$education->getId(), $request->request->get('_token'))) {
            $entityManager->remove($education);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_education_index', [], Response::HTTP_SEE_OTHER);
    }
}
