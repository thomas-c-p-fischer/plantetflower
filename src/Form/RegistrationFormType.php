<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'acheteur/vendeur' => 'Buyer',
                    'vendeur' => 'Owner',
                    'acheteur' => 'Buyer'
                ]
            ])
            ->add('lastname', TextType::class, array(
                'constraints' => array(
                    new NotBlank())))
            ->add('firstname', TextType::class, array(
                'constraints' => array(
                    new NotBlank())))
            ->add('countryOfResidence', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'France' => 'FR',
                    'Grande-Bretagne' => 'GB',
                    'Italie' => 'ITA'
                ]
            ])
            ->add('birthday', BirthdayType::class, array(
                'constraints' => array(
                    new NotBlank())))
            ->add('nationality', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Française' => 'FR',
                    'Britannique' => 'GB',
                    'Italienne' => 'ITA'
                ]
            ])
            ->add('email')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => false,
                'help' => "J'accepte les <a href='/cgu'>Conditions Générales d'Utilisation*</a>",
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions Générales d\'Utilisation.',
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
            ])
            ->add('recaptcha', ReCaptchaType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
