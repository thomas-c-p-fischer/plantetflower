<?php

namespace App\Form;

use App\Entity\Annonce;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class CreateAnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('images', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'attr' => array('accept' => 'image/jpeg,image/png,image/jpg'),
                'required' => true,
            ])
            ->add('description', null, [
                'required' => false,
            ])
            ->add('shipement', CheckboxType::class, [
                'required' => false,
            ])
            ->add('priceOrigin', MoneyType::class, [
                'required' => true,
                'invalid_message' => 'Nous ne prenons pas en compte votre annonce au-delà de 100€, veuillez nous excuser'
            ])
            ->add('plantPot', CheckboxType::class, [
                'required' => false,
            ])
            ->add('dateExpiration', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('title', null, [
                'required' => true,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'content',
                'required' => true,
            ])
            ->add('ville', null, [
                'required' => true,
            ])
            ->add('poids', ChoiceType::class, [
                'placeholder' => 'Choisissez le poids de votre plante',
                'choices' => [
                    '0g - 500g' => '0g - 500g',
                    '500g - 1kg' => '500g - 1kg',
                    '1kg - 2kg' => '1kg - 2kg',
                    '2kg - 3kg' => '2kg - 3kg',
                ],
                'required' => true,
            ])
            ->add('expAdress', null, [
                'required' => true,
                'empty_data' => '',
            ])
            ->add('expZipCode', null, [
                'required' => true,
                'empty_data' => '',
            ])
            ->add('expRelId', HiddenType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('Envoyer', SubmitType::class, ['attr' => ['class' => "button"]]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
