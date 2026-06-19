<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ValidExampleValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidExample) {
            throw new UnexpectedTypeException($constraint, ValidExample::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value) || str_contains($value, 'invalid')) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
