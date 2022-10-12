<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaiementFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('POST')
            ->setAction('cardRegistrationUrl')
            ->add('accessKey', HiddenType::class, [
                'attr' => [
                    'value' => 'AccessKey'
                ]
            ])
            ->add('preregistrationData', HiddenType::class, [
                'attr' => [
                    'value' => 'PreregistrationData'
                ]
            ])
            ->add('returnUrl', HiddenType::class, [
                'attr' => [
                    'value' => '$returnUrl'
                ]
            ])
            ->add('cardNumber', TextType::class, [
                'required' => true
            ])
            ->add('expirationDate', TextType::class, [
                'required' => true
            ])
            ->add('CVC', TextType::class, [
                'required' => true
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Valider le paiement',
                'attr' => [
                    'value' => 'Pay'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
