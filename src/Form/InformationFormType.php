<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

//Formulaire d'ajout des informations requises pour pouvoir vendre sur le site
class InformationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('IBAN', TextType::class, [
                'required' => true,
                'label' => false,
                'label_attr' => [
                    'class' => 'iban-text'
                ]
            ])
            ->add('KYCrecto', FileType::class
            )
            ->add('KYCverso', FileType::class)
            ->add('send', SubmitType::class, [
                'label' => 'Valider vos informations'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}
