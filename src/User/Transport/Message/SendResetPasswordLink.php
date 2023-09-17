<?php

declare(strict_types=1);

namespace App\User\Transport\Message;

use App\User\Domain\Entity\User;

final class SendResetPasswordLink
{
    public function __construct(private readonly User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
