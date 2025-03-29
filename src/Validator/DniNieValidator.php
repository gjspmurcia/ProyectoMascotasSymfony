<?php
namespace App\Validator;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Doctrine\ORM\Query\AST\Functions\LengthFunction;
use Symfony\Component\Validator\Constraints\Length;


class DniNieValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DniNie) {
            throw new UnexpectedTypeException($constraint, DniNie::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) 
        {
            throw new UnexpectedValueException($value, 'string');

        }

        $value = strtoupper(trim($value));
    
        // Expresión regular para DNI y NIE
        $regex = '/^[XYZ]?[0-9]{7,8}[A-Z]$/';
        
        if (! preg_match($regex, $value)) 
        {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value)
            ->addViolation();
        }

        if ($value[0] == 'X') {
            $value = '0' . substr($value, 1); // Sustituir X por 0
        } elseif ($value[0] == 'Y') {
            $value = '1' . substr($value, 1); // Sustituir Y por 1
        } elseif ($value[0] == 'Z') {
            $value = '2' . substr($value, 1); // Sustituir Z por 2
        }else {
            $numero = substr($value, 0, 8); // Para DNI, tomar los primeros 8 caracteres
        }

        $letras = "TRWAGMYFPDXBNJZSQVHLCKE";
        $letra = substr($value, -1);
        
        if(  $letras[$numero % 23] !== $letra )
        {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value)
            ->addViolation();
        }

        
    }
}



