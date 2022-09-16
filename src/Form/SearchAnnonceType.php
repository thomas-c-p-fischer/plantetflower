<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchAnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        // ->add('title')
        ->add('title', SearchType::class, [
            'label' => false,
            'attr' => [
                'class' => 'form-search',
                'placeholder' => 'Recherchez votre végétal'
            ],
            'required' => false
        ])
        ->add('Rechercher', SubmitType::class, [
            'attr' => [
                'class' => 'btn-search',
            ]
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
