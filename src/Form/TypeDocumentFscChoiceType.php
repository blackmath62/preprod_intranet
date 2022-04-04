<?php

namespace App\Form;

use App\Entity\Main\TypeDocumentFsc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TypeDocumentFscChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('title',EntityType::class,[
            'class' => TypeDocumentFsc::class,
            'choice_label' => 'title',
            'mapped' => false,
            'placeholder' => 'Veuillez selectionner un type de document Fsc',
            'choice_name' => 'id',
            'expanded' => false,
            'multiple' => false,
            'label' => 'Type de document Fsc',
            'attr' => ['class' => 'mr-3 form-control col-12 col-sm-12 text-center'],
            'label_attr' => ['class' => 'col-12 col-sm-12 text-center mt-3'] 
            ])
        ->add('modifier', SubmitType::class,[
            'attr' => ['class' => 'form-control btn btn-dark mt-3']
        ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TypeDocumentFsc::class,
        ]);
    }
}
