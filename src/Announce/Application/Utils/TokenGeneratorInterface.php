<?php

declare(strict_types=1);

namespace App\Announce\Application\Utils;

interface TokenGeneratorInterface
{
    public function generateToken(): string;
}
