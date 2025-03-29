<?php
namespace App\Validator;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Doctrine\ORM\Query\AST\Functions\LengthFunction;
use Symfony\Component\Validator\Constraints\Length;


class TelefonoValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Telefono) {
            throw new UnexpectedTypeException($constraint, Telefono::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) 
        {
            throw new UnexpectedValueException($value, 'string');

        }

        if (!preg_match('/^(6|7|8|9)[0-9]{8}$/' , $value) ) 
        {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}



