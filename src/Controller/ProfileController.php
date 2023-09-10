<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(#[CurrentUser] ?User $user): Response
    {
        return $this->render('profile/index.html.twig', [
            'profile' => $user,
        ]);
    }
}
