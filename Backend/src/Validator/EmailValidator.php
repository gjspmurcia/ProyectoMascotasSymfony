<?php
namespace App\Validator;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Doctrine\ORM\Query\AST\Functions\LengthFunction;
use Symfony\Component\Validator\Constraints\Length;


class EmailValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Email) {
            throw new UnexpectedTypeException($constraint, Email::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) 
        {
            throw new UnexpectedValueException($value, 'string');

        }

        $value = (trim($value));
    
        // Expresión regular para email
        $regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        
        if (! preg_match($regex, $value)) 
        {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value)
            ->addViolation();
        }

        
    }
}



