<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Acheteur/Vendeur' => 'acheteur/vendeur',
                    'Vendeur' => 'vendeur',
                    'Acheteur' => 'acheteur'
                ]
            ])
            ->add('lastname', TextType::class, array(
                'constraints' => array(
                    new NotBlank())))
            ->add('firstname', TextType::class, array(
                'constraints' => array(
                    new NotBlank())))
            ->add('streetnumber', TextType::class, array(
                'constraints' => array(
                    new NotBlank())))
            ->add('address', TextType::class, array(
                'constraints' => array(
                    new NotBlank())))
            ->add('address2', TextType::class, array(
                'required' => false))
            ->add('zipcode', TextType::class, array(
                'constraints' => array(
                    new NotBlank())))
            ->add('city', TextType::class, array(
                'constraints' => array(
                    new NotBlank())))
            ->add('email')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions d`utilisation.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
