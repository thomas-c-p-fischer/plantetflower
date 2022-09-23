<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', TextType::class, array(
                    'constraints' => array(
                        new NotBlank(),
                        new Regex(array(
                                'pattern' => '/^[a-zA-Z_\-\'., \/]{0,32}$/',
                                'message' => 'Votre Nom ne doit pas contenir de chiffre et avoir 32 caractères au maximum : {{ value }}'
                            )
                        ),
                    ))
            )
            ->add('firstname', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[a-zA-Z_\-\'., \/]{0,32}$/',
                            'message' => 'Votre Prénom ne doit pas contenir de chiffre et avoir 32 caractères au maximum : {{ value }}'
                        )
                    ),
                )))
            ->add('streetnumber', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[0-9a-zA-Z_\-\'., \/]{0,32}$/',
                            'message' => 'Votre Numéro de rue n\'est pas valide : {{ value }}'
                        )
                    ),
                )))
            ->add('address', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[0-9a-zA-Z_\-\'., \/]{0,32}$/',
                            'message' => 'Votre adresse ne doit pas dépasser 32 caractères : {{ value }}, veuillez remplir le complément d\'adresse ci-dessous.'
                        )
                    ),
                )))
            ->add('address2', TextType::class, array(
                'constraints' => array(
                    new Regex(array(
                            'pattern' => '/^[0-9a-zA-Z_\-\'., \/]{0,32}$/',
                            'message' => 'Votre complément d\'adresse ne doit pas dépasser 32 caractères : {{ value }}'
                        )
                    ),
                ),
                'required' => false
            ))
            ->add('zipcode', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[0-9@]{1}[0-9]{4}$/',
                            'message' => 'Votre code postale n\'est pas valide : {{ value }}'
                        )
                    ),
                )))
            ->add('city', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[a-zA-Z_\-\' ]{2,50}$/',
                            'message' => 'Votre ville ne doit pas dépasser 50 caractères : {{ value }}'
                        )
                    ),
                )))
            ->add('status', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Acheteur/Vendeur' => 'acheteur/vendeur',
                    'Vendeur' => 'vendeur',
                    'Acheteur' => 'acheteur',
                ]
            ])
            ->add('email', TextType::class, array(
                'label' => false,
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[\w\-\.\@_]{7,70}$/',
                            'message' => 'Votre {{ label }} n\'est pas valide : {{ value }}'
                        )
                    ),
                )), null, array('label' => false))
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
                // au lieu d’être placé directement sur l’objet,
                // ceci est lu et encodé dans le contrôleur
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // longueur maximale autorisée par Symfony pour des raisons de sécurité
                        'max' => 4096,
                    ]),
                ],
            ]);
        // personnalisation du captcha

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}