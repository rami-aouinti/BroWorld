<?php

declare(strict_types=1);

namespace App\User\Application\Validator;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class RegisteredUserValidator extends ConstraintValidator
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        /* @var $constraint RegisteredUser */

        if (null === $value || '' === $value) {
            return;
        }

        $existingUser = $this->userRepository->findOneBy(['email' => $value]);

        if (!$existingUser instanceof User) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
