<?php

declare(strict_types=1);

namespace App\User\Transport\Controller\Admin\Ajax\Admin;

use App\Announce\Transport\Controller\AbstractPhotoController;
use App\User\Transport\Controller\Admin\Ajax\AjaxController;
use App\Announce\Domain\Entity\Property;
use App\User\Application\Service\FileUploader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PhotoController extends AbstractPhotoController implements AjaxController
{
    #[Route(path: '/admin/photo/{id<\d+>}/upload', name: 'admin_photo_upload', methods: ['POST'])]
    public function upload(Property $property, Request $request, FileUploader $fileUploader): JsonResponse
    {
        return $this->uploadPhoto($property, $request, $fileUploader);
    }

    /**
     * Sort photos.
     */
    #[Route(path: '/admin/photo/{id<\d+>}/sort', name: 'admin_photo_sort', methods: ['POST'])]
    public function sort(Request $request, Property $property): JsonResponse
    {
        return $this->sortPhotos($request, $property);
    }
}
