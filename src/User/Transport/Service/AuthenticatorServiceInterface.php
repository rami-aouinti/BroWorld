<?php

declare(strict_types=1);

namespace App\User\Transport\Service;

use App\User\Domain\Entity\User;

interface AuthenticatorServiceInterface
{
    public function getAuthUser(): ?User;

}
