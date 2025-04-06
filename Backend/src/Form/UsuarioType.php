<?php

namespace App\Form;

use App\Entity\Usuario;

use App\Validator\Email;
use App\Validator\DniNie;
use App\Validator\Telefono;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class UsuarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, ['label' => 'Correo electronico', 'attr' => ['class' => 'form-control'],'constraints' => [new Email()]])
            ->add('password', PasswordType::class, ['label' => 'Contraseña', 'attr' => ['class' => 'form-control']])
            ->add('nombre', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('dni', TextType::class, ['attr' => ['class' => 'form-control'],'constraints' => [new DniNie()]])
            ->add('telefono', TextType::class, ['attr' => ['class' => 'form-control'], 'constraints' => [new Telefono()]])
            ->add('calle', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('num_calle', IntegerType::class, ['label' => 'Nº calle', 'attr' => ['class' => 'form-control']])
            ->add('cod_postal', IntegerType::class, ['label' => 'Código postal', 'attr' => ['class' => 'form-control']])
            ->add('cuidad', TextType::class, ['attr' => ['class' => 'form-control']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
