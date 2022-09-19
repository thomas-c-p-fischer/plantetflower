<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Gregwar\CaptchaBundle\Type\CaptchaType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', TextType::class,array (
                    'constraints' => array(
                        new NotBlank(),
                        new Regex(array(
                                'pattern' => '/^[a-zA-Z_\-\'., \/]{0,32}$/',
                                'message' => 'Votre Nom ne doit pas contenir de chiffre et avoir 32 caractères au maximum : {{ value }}'
                            )
                        ),
                    ))
            )
            ->add('firstname', TextType::class,array (
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[a-zA-Z_\-\'., \/]{0,32}$/',
                            'message' => 'Votre Prénom ne doit pas contenir de chiffre et avoir 32 caractères au maximum : {{ value }}'
                        )
                    ),
                )))
            ->add('streetnumber', TextType::class,array (
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[0-9a-zA-Z_\-\'., \/]{0,32}$/',
                            'message' => 'Votre Numéro de rue n\'est pas valide : {{ value }}'
                        )
                    ),
                )))
            ->add('address', TextType::class,array (
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[0-9a-zA-Z_\-\'., \/]{0,32}$/',
                            'message' => 'Votre adresse ne doit pas dépasser 32 caractères : {{ value }}, veuillez remplir le complément d\'adresse ci-dessous.'
                        )
                    ),
                )))
            ->add('address2', TextType::class,array (
                'constraints' => array(
                    new Regex(array(
                            'pattern' => '/^[0-9a-zA-Z_\-\'., \/]{0,32}$/',
                            'message' => 'Votre complément d\'adresse ne doit pas dépasser 32 caractères : {{ value }}'
                        )
                    ),
                ),
                'required'=>false
            ))
            ->add('zipcode', TextType::class,array (
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[0-9@]{1}[0-9]{4}$/',
                            'message' => 'Votre code postale n\'est pas valide : {{ value }}'
                        )
                    ),
                )))
            ->add('city', TextType::class,array (
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^[a-zA-Z_\-\' ]{2,26}$/',
                            'message' => 'Votre ville ne doit pas dépasser 26 caractères : {{ value }}'
                        )
                    ),
                )))
            ->add('phonenumber', TextType::class,array (
                'constraints' => array(
                    new NotBlank(),
                    new Regex(array(
                            'pattern' => '/^((00|\+)33|0)[0-9][0-9]{8}$/',
                            'message' => 'Votre numéro de téléphone n\'est pas valide : {{ value }}'
                        )
                    ),
                )))
            ->add('sex', ChoiceType::class, [
                'placeholder' => 'Civilité',
                'choices' => [
                    'Monsieur' => 'M',
                    'Madame' => 'MME',
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Vous êtes ...',
                'choices' => [
                    'Acheteur/Vendeur' => 'acheteur/vendeur',
                    'Vendeur' => 'vendeur',
                    'Acheteur' => 'acheteur',
                ]
            ])
            ->add('verified', CheckboxType::class, [
                'required' => false,

            ])
            ->add('idMangoPay', TextAreaType::class, [
                'required' => false,
            ])
            ->add('birthday', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Naissance :',
                'placeholder' => [
                    'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
                ],
                'years' => range(date('Y'), date('Y') - 100),
            ])
            ->add('nationality', CountryType::class, [
                'placeholder' => 'Nationalité',
                'choices' => [
                    'Afghanistan' => 'AF',
                    'Afrique du Sud' => 'SF',
                    'Albanie' => 'AL',
                    'Algérie' => 'AG',
                    'Allemagne' => 'GE',
                    'Andorre' => 'AN',
                    'Angola' => 'AO',
                    'Antigua-et-Barbuda' => 'AC',
                    'Arabie saoudite' => 'SA',
                    'Argentine' => 'AR',
                    'Arménie' => 'AM',
                    'Australie' => 'AS',
                    'Azerbaïdjan' => 'AJ',
                    'Autriche' => 'AT',
                    'Bahamas' => 'BF',
                    'Bahreïn' => 'BA',
                    'Bangladesh' => 'BG',
                    'Barbade' => 'BB',
                    'Belgique' => 'BE',
                    'Belize' => 'BH',
                    'Bénin' => 'BN',
                    'Bhoutan' => 'BT',
                    'Biélorussie' => 'BO',
                    'Birmanie' => 'BM',
                    'Bolivie' => 'BL',
                    'Bosnie-Herzégovine' => 'BK',
                    'Bosnie-Îles Mariannes du Nord' => 'CQ',
                    'Botswana' => 'BC',
                    'Brésil' => 'BR',
                    'Brunei' => 'BX',
                    'Bulgarie' => 'BU',
                    'Burkina Faso' => 'UV',
                    'Burundi' => 'BY',
                    'Cambodge' => 'CB',
                    'Cameroun' => 'CM',
                    'Canada' => 'CA',
                    'Cap-Vert' => 'CV',
                    'Chine' => 'CH',
                    'Chili' => 'CL',
                    'Chypre' => 'CY',
                    'Colombie' => 'CO',
                    'Comores' => 'CN',
                    'Corée du Nord' => 'KN',
                    'Corée du Sud' => 'KS',
                    'Costa Rica' => 'CS',
                    'Côte d\'Ivoire' => 'IV',
                    'Croatie' => 'HR',
                    'Cuba' => 'CU',
                    'Danemark' => 'DA',
                    'Djibouti' => 'DJ',
                    'Dominique' => 'DO',
                    'Équateur' => 'EC',
                    'Égypte' => 'EG',
                    'Émirats arabes unis' => 'TC',
                    'Érythrée' => 'ER',
                    'Espagne' => 'ES',
                    'Estonie' => 'EN',
                    'Eswatini' => 'WZ',
                    'États fédérés de Micronésie' => 'FM',
                    'États-Unis' => 'US',
                    'Éthiopie' => 'ET',
                    'Fidji' => 'FJ',
                    'Finlande' => 'FI',
                    'France' => 'FR',
                    'Gabon' => 'GB',
                    'Gambie' => 'GA',
                    'Géorgie' => 'GG',
                    'Ghana' => 'GH',
                    'Grèce' => 'GR',
                    'Grenade' => 'GJ',
                    'Guatemala' => 'GT',
                    'Guinée' => 'GV',
                    'Guinée-Bissau' => 'PU',
                    'Guinée équatoriale' => 'EK',
                    'Guyana' => 'GY',
                    'Haïti' => 'HA',
                    'Honduras' => 'HO',
                    'Hongrie' => 'HU',
                    'Îles Marshall' => 'RM',
                    'Îles Salomon' => 'BP',
                    'Inde' => 'IN',
                    'Indonésie' => 'ID',
                    'Irak' => 'IZ',
                    'Iran' => 'IR',
                    'Irlande' => 'IE',
                    'Islande' => 'IC',
                    'Israël' => 'IS',
                    'Italie' => 'IT',
                    'Jamaïque' => 'JM',
                    'Japon' => 'JA',
                    'Jordanie' => 'JO',
                    'Kazakhstan' => 'KZ',
                    'Kenya' => 'KE',
                    'Kirghizistan' => 'KG',
                    'Kiribati' => 'KR',
                    'Koweït' => 'KU',
                    'Laos' => 'LA',
                    'Lesotho' => 'LT',
                    'Lettonie' => 'LG',
                    'Liban' => 'LE',
                    'Liberia' => 'LI',
                    'Libye' => 'LY',
                    'Liechtenstein' => 'LS',
                    'Lituanie' => 'LH',
                    'Luxembourg' => 'LU',
                    'Macédoine du Nord' => 'MK',
                    'Madagascar' => 'MA',
                    'Malaisie' => 'MY',
                    'Malawi' => 'MI',
                    'Maldives' => 'MV',
                    'Mali' => 'ML',
                    'Malte' => 'MT',
                    'Maroc' => 'MO',
                    'Maurice' => 'MP',
                    'Mauritanie' => 'MR',
                    'Mexique' => 'MX',
                    'Monaco' => 'MN',
                    'Mongolie' => 'MG',
                    'Mozambique' => 'MZ',
                    'Namibie' => 'WS',
                    'Nauru' => 'NR',
                    'Népal' => 'NP',
                    'Nicaragua' => 'NU',
                    'Niger' => 'NG',
                    'Nigeria' => 'NI',
                    'Norvège' => 'NO',
                    'Nouvelle-Zélande' => 'NZ',
                    'Oman' => 'MU',
                    'Ouganda' => 'UG',
                    'Ouzbékistan' => 'UZ',
                    'Pakistan' => 'PK',
                    'Palaos' => 'PW',
                    'Panama' => 'PM',
                    'Papouasie-Nouvelle-Guinée' => 'PP',
                    'Paraguay' => 'PA',
                    'Pays-Bas' => 'NL',
                    'Pérou' => 'PE',
                    'Philippines' => 'RP',
                    'Pologne' => 'PL',
                    'Porto Rico' => 'RQ',
                    'Portugal' => 'PO',
                    'Qatar' => 'QA',
                    'République centrafricaine' => 'CT',
                    'République de Moldavie' => 'MD',
                    'République démocratique du Congo' => 'CG',
                    'République dominicaine' => 'DR',
                    'République du Congo' => 'CF',
                    'République tchèque' => 'CZ',
                    'Roumanie' => 'RO',
                    'Royaume-Uni' => 'UK',
                    'Russie' => 'RS',
                    'Rwanda' => 'RW',
                    'Saint-Christophe-et-Niévès' => 'SC',
                    'Sainte-Lucie' => 'ST',
                    'Saint-Marin' => 'SM',
                    'Saint-Vincent-et-les-Grenadines' => 'VC',
                    'Salvador' => 'SV',
                    'Samoa' => 'WS',
                    'Sao Tomé-et-Principe' => 'TP',
                    'Sénégal' => 'SG',
                    'Serbie' => 'SR',
                    'Seychelles' => 'SE',
                    'Sierra Leone' => 'SL',
                    'Singapour' => 'SN',
                    'Slovaquie' => 'SK',
                    'Slovénie' => 'SI',
                    'Somalie' => 'SO',
                    'Soudan' => 'SU',
                    'Sri Lanka' => 'CE',
                    'Suède' => 'SE',
                    'Suisse' => 'SZ',
                    'Suriname' => 'NS',
                    'Syrie' => 'SY',
                    'Tadjikistan' => 'TI',
                    'Taïwan' => 'TW',
                    'Tanzanie' => 'TZ',
                    'Tchad' => 'CD',
                    'Thaïlande' => 'TH',
                    'Timor oriental' => 'TM',
                    'Togo' => 'TO',
                    'Tonga' => 'TN',
                    'Trinité-et-Tobago' => 'TD',
                    'Tunisie' => 'TS',
                    'Turkménistan' => 'TX',
                    'Turquie' => 'TU',
                    'Tuvalu' => 'TV',
                    'Ukraine' => 'UA',
                    'Uruguay' => 'UY',
                    'Vanuatu' => 'NH',
                    'Vatican' => 'VT',
                    'Venezuela' => 'VE',
                    'Viêt Nam' => 'VN',
                    'Yémen' => 'YE',
                    'Zambie' => 'ZA',
                    'Zimbabwe' => 'ZI',
                ]
            ])
            ->add('email', TextType::class,array (
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
                'help' => "J'accepte les <a href='/'>Conditions Générales d'Utilisation*</a>",
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
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
            ])
            // personnalisation du captcha
            ->add('captcha', CaptchaType::class, array(
                'width' => 200,
                'height' => 50,
                'length' => 4,
                'invalid_message' => 'Le code captcha ne correspond pas',
                ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}