<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModeRemiseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->setAction('annonce_paiement')
            ->add('mainPropre', CheckboxType::class, ['required' => false, 'attr' => ['multiple' => false]])
            ->add('mainPropre2', HiddenType::class, ['label' => 'Remise en main propre'])
            ->add('mondialRelay', CheckboxType::class, ['required' => false, 'attr' => ['multiple' => false]])
            ->add('payer', SubmitType::class, ['label' => 'PAYER', 'attr' => ['value' => 'Payer']])
            ->add('relais', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
