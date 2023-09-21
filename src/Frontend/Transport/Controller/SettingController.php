<?php

namespace App\Frontend\Transport\Controller;

use App\Frontend\Domain\Entity\Setting;
use App\Frontend\Domain\Repository\SettingRepository;
use App\Frontend\Transport\Form\SettingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/setting')]
class SettingController extends AbstractController
{
    #[Route('/', name: 'app_setting_index', methods: ['GET'])]
    public function index(SettingRepository $settingRepository): Response
    {
        return $this->render('frontend/setting/index.html.twig', [
            'settings' => $settingRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_setting_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $setting = new Setting();
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($setting);
            $entityManager->flush();

            return $this->redirectToRoute('app_setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('frontend/setting/new.html.twig', [
            'setting' => $setting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_setting_show', methods: ['GET'])]
    public function show(Setting $setting): Response
    {
        return $this->render('frontend/setting/show.html.twig', [
            'setting' => $setting,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_setting_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Setting $setting, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('frontend/setting/edit.html.twig', [
            'setting' => $setting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_setting_delete', methods: ['POST'])]
    public function delete(Request $request, Setting $setting, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$setting->getId(), $request->request->get('_token'))) {
            $entityManager->remove($setting);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_setting_index', [], Response::HTTP_SEE_OTHER);
    }
}
