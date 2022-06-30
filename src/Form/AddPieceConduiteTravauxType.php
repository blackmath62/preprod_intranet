<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use App\Entity\Main\ConduiteTravauxAddPiece;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AddPieceConduiteTravauxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numPieceCmd', EntityType::class, [
                'class' => ConduiteTravauxAddPiece::class,
                'choice_label' => 'numPiece',
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('ct')
                    ->where('ct.type = 2');
                },
                'choice_name' => 'id',
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'required' => false,
                'attr' => [
                    'class' => 'select-Piece-Cmd form-control',
                ],
                'label' => false,
            ])
            ->add('numPieceBL', EntityType::class, [
                'class' => ConduiteTravauxAddPiece::class,
                'choice_label' => 'numPiece',
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('ct')
                    ->where('ct.type = 3');
                },
                'choice_name' => 'id',
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'required' => false,
                'attr' => [
                    'class' => 'select-Piece-BL form-control',
                    'data-dropdown-css-class' => "select2-purple"
                ],
                'label' => false,
            ])
            ->add('numPieceFacture', EntityType::class, [
                'class' => ConduiteTravauxAddPiece::class,
                'choice_label' => 'numPiece',
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('ct')
                    ->where('ct.type = 4');
                },
                'choice_name' => 'id',
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'required' => false,
                'attr' => [
                    'class' => 'select-Piece-Facture form-control',
                ],
                'label' => false,
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
