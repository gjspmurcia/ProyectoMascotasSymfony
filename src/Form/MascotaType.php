<?php

namespace App\Form;

use App\Entity\Mascota;
use App\Entity\QR;
use App\Entity\Usuario;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class MascotaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('num_chip', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('observaciones', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('foto', FileType::class, [
                'label' => 'Foto de la mascota',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mascota::class,
        ]);
    }
}
