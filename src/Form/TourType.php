<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Tour;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TourType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('img', FileType::class, [
                'label' => 'Product Picture',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Only PNG and JPG are allowed',
                        'maxSize' => '2048k'
                    ])
                ],
            ])
            ->add('price')
            ->add('duration')
            ->add('title')
            ->add('minPerson')
            ->add('maxPerson')
            ->add('wheelchairAcces')
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tour::class,
        ]);
    }
}
