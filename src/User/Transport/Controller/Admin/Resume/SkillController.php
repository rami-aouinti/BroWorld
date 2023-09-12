<?php

namespace App\User\Transport\Controller\Admin\Resume;

use App\Resume\Model\Entity\Skill;
use App\Resume\Model\Repository\SkillRepository;
use App\Resume\Transport\Form\SkillType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/skill')]
class SkillController extends AbstractController
{
    #[Route('/', name: 'admin_skill_index', methods: ['GET'])]
    public function index(SkillRepository $skillRepository): Response
    {
        return $this->render('admin/skill/index.html.twig', [
            'skills' => $skillRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_skill_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $skill = new Skill();
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($skill);
            $entityManager->flush();

            return $this->redirectToRoute('admin_skill_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/skill/new.html.twig', [
            'skill' => $skill,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_skill_show', methods: ['GET'])]
    public function show(Skill $skill): Response
    {
        return $this->render('admin/skill/show.html.twig', [
            'skill' => $skill,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_skill_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Skill $skill, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_skill_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/skill/edit.html.twig', [
            'skill' => $skill,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_skill_delete', methods: ['POST'])]
    public function delete(Request $request, Skill $skill, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$skill->getId(), $request->request->get('_token'))) {
            $entityManager->remove($skill);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_skill_index', [], Response::HTTP_SEE_OTHER);
    }
}
