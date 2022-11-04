<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

//Ce formulaire sert à l'édition du profil utilisateur
class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //Champ du formulaire pour ajouter une image
            ->add('image', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false
            ])
            //champ du nom
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
            //Champ du prénom
            ->add('firstname', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[a-zA-Z_\-\'., \/]{0,32}$/',
                            'message' => 'Votre Prénom ne doit pas contenir de chiffre et avoir 32 caractères au maximum : {{ value }}'
                        )
                    ),
                )))
            //Les 2 champs suivants sont pour l'adresse en respectant le format donné par le regex
            ->add('address', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[0-9a-zA-Z_\-\'., \/]{0,32}$/',
                            'message' => 'Votre adresse ne doit pas dépasser 32 caractères, veuillez remplir le complément d\'adresse ci-dessous : {{ value }}'
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
            //Ce champ concerne le code postal
            ->add('zipcode', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[0-9@]{1}[0-9]{4}$/',
                            'message' => 'Votre code postale n\'est pas valide : {{ value }}'
                        )
                    ),
                )))
            //Ici l'user rentre sa ville
            ->add('city', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[a-zA-Z_\-\' ]{2,26}$/',
                            'message' => 'Votre ville ne doit pas dépasser 26 caractères : {{ value }}'
                        )
                    ),
                )))
            //Ce champ permet de mettre un numéro de téléphone en respectant le format donné par le regex
            ->add('phonenumber', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^((00|\+)33|0)[0-9][0-9]{8}$/',
                            'message' => 'Votre numéro de téléphone n\'est pas valide : {{ value }}'
                        )
                    ),
                )))
            //choix du genre de l'user
            ->add('gender', ChoiceType::class, [
                'placeholder' => 'Civilité',
                'choices' => [
                    'Monsieur' => 'M',
                    'Madame' => 'MME',
                ]
            ])
//            //Ce champ du formulaire permettra à l'user de choisir son statut
//            ->add('owner', ChoiceType::class, [
//                'placeholder' => 'Vous souhaiteriez être...',
//                'choices' => [
//                    'vendeur' => 'vendeur',
//                    'acheteur' => 'acheteur',
//                    'acheteur/vendeur' => 'acheteur/vendeur',
//                ]
//            ])
//            ->add('payer', ChoiceType::class, [
//                'placeholder' => 'Vous souhaiteriez être...',
//                'choices' => [
//                    'vendeur' => 'vendeur',
//                    'acheteur' => 'acheteur',
//                    'acheteur/vendeur' => 'acheteur/vendeur',
//                ]
//            ])
            //Champ d'ajout de l'email user avec regex pour format
            ->add('email', TextType::class, array(
                'label' => false,
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[\w\-\.\@_]{7,70}$/',
                            'message' => 'Votre {{ label }} n\'est pas valide : {{ value }}'
                        )
                    ),
                )))
            //Champ pour le mot de passe
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le mot de passe est obligatoire',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comporter {{ limit }} caractères minimum',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])//->add('captcha', ReCaptchaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
