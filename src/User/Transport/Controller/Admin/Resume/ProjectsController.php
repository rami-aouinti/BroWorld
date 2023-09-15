<?php

namespace App\User\Transport\Controller\Admin\Resume;

use App\Resume\Domain\Entity\Projects;
use App\Resume\Domain\Repository\ProjectsRepository;
use App\Resume\Transport\Form\ProjectsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/projects')]
class ProjectsController extends AbstractController
{
    #[Route('/', name: 'admin_projects_index', methods: ['GET'])]
    public function index(ProjectsRepository $projectsRepository): Response
    {
        return $this->render('admin/projects/index.html.twig', [
            'projects' => $projectsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_projects_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Projects();
        $form = $this->createForm(ProjectsType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('admin_projects_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/projects/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_projects_show', methods: ['GET'])]
    public function show(Projects $project): Response
    {
        return $this->render('admin/projects/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_projects_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projects $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectsType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_projects_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/projects/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_projects_delete', methods: ['POST'])]
    public function delete(Request $request, Projects $project, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_projects_index', [], Response::HTTP_SEE_OTHER);
    }
}
