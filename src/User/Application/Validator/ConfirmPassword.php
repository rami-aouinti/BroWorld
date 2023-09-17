<?php

declare(strict_types=1);

namespace App\User\Application\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ConfirmPassword extends Constraint
{
    public string $message = 'password.mismatch';
}
