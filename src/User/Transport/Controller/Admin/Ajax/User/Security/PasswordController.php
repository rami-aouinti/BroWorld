<?php

declare(strict_types=1);

namespace App\User\Transport\Controller\Admin\Ajax\User\Security;

use App\User\Transport\Controller\Admin\Ajax\AjaxController;
use App\User\Application\Service\User\PasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PasswordController extends AbstractController implements AjaxController
{
    #[Route(path: '/user/password', name: 'user_password', methods: ['POST'])]
    public function update(Request $request, PasswordService $service): JsonResponse
    {
        try {
            $service->update($request);

            return new JsonResponse([]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}