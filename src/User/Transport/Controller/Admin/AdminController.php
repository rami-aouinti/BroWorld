<?php

namespace App\User\Transport\Controller\Admin;

use App\Announce\Transport\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends BaseController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request): Response
    {
        return $this->render('admin/index.html.twig', [
            'site' => $this->site($request),
            'controller_name' => 'AdminController',
        ]);
    }
}
