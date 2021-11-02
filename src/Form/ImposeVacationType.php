<?php

namespace App\Form;

use App\Entity\Main\Users;
use App\Entity\Main\Holiday;
use App\Entity\Main\HolidayTypes;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ImposeVacationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            
            ->add('start', DateTimeType::class, [
                'placeholder' => [
                    'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
                    'hour' => 'Heure', 'minute' => 'Minute', 'second' => 'Seconde'],
                'date_widget' => 'single_text',
                'time_label' => 'Heure de début',
                'time_widget' => 'single_text',
                'required' => true,
                //'data' =>  date_time_set(new \DateTime("now"), 00, 00),
                'label' => "Date début",
                'attr' => ['class' => 'col-3 ml-3'],
            ])
            ->add('end', DateTimeType::class,[
                'placeholder' => [
                    'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
                    'hour' => 'Heure', 'minute' => 'Minute', 'second' => 'Seconde'],
                'date_widget' => 'single_text',
                'time_label' => 'Heure de début',
                'time_widget' => 'single_text',
                'required' => true,
                //'data' => date_time_set(new \DateTime("now"), 23, 59),
                'label' => "Date fin",
                'attr' => ['class' => 'col-3 ml-3'],
        ])
            ->add('holidayType',EntityType::class,[
                'class' => HolidayTypes::class,
                'choice_label' => 'name',
                'label' => 'Type de demande',
                'attr' => ['class' => 'ml-3 ']
            ])
            ->add('user', EntityType::class, [
                // looks for choices from this entity
                'class' => Users::class,
                'choice_label' => 'email',
                'expanded' => true,
                'multiple' => true,
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('u')
                            ->orderBy('u.email', 'ASC');
                }
            ])
            ->add('details', HiddenType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'col-12 form-control textarea',
                    'placeholder' => 'Vous pouvez saisir les précisions sur votre demande de congés si cela est nécéssaire'
                ],
                'label' => 'Détail de la demande',
            ])
            ->add('Envoyer', SubmitType::class,[
                'attr' => ['class' => 'col-1 form-control btn btn-dark m-3 float-right']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Holiday::class,
        ]);
    }
}