<?php

namespace App\User\Transport\Controller;

use App\Frontend\Infrastructure\Service\FileUploader;
use App\User\Domain\Entity\User;
use App\User\Transport\Form\ChangePasswordType;
use App\User\Transport\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(#[CurrentUser] ?User $user): Response
    {
        return $this->render('profile/index.html.twig', [
            'profile' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(
        #[CurrentUser] ?User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('imageFile')->getData();

            if($image) {
                dd('hi');
                $brochureFileName = $fileUploader->upload($image);
            $user->setAvatar($brochureFileName);
            $user->setPhoto($brochureFileName);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/profile/change-password', name: 'profile_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        LogoutUrlGenerator $logoutUrlGenerator,
    ): Response {
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirect($logoutUrlGenerator->getLogoutPath());
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form,
        ]);
    }
}
