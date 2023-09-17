<?php

declare(strict_types=1);

namespace App\User\Application\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RegisteredUser extends Constraint
{
    public string $message = 'user_not_found';
}
