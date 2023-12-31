<?php

namespace App\Form;

use App\Entity\Main\Prestataire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SendTicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("prestataire", EntityType::class, [
                'class' => Prestataire::class,
                'choice_label' => 'nom',
                'choice_name' => 'nom',
                'attr' => ['class' => 'form-control mt-3'],

            ])

            ->add('Send', SubmitType::class, [
                'label' => "Envoyer par email",
                'attr' => ['class' => 'form-control btn btn-info mt-3'],
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
