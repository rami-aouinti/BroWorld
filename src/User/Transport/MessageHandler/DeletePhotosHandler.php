<?php

declare(strict_types=1);

namespace App\User\Transport\MessageHandler;

use App\User\Application\Service\FileUploader;
use App\User\Transport\Message\DeletePhotos;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeletePhotosHandler
{
    public function __construct(private readonly FileUploader $fileUploader)
    {
    }

    public function __invoke(DeletePhotos $deletePhotos): void
    {
        $photos = $deletePhotos->getProperty()->getPhotos();

        foreach ($photos as $photo) {
            $this->fileUploader->remove($photo->getPhoto());
        }
    }
}
