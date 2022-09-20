<?php

namespace App\Form;

use App\Entity\Annonce;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnonceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('shipement')
            ->add('delivery_status')
            ->add('price_origin')
            ->add('price_total')
            ->add('created_at')
            ->add('plant_pot')
            ->add('expiration_date')
            ->add('sold')
            ->add('poids')
            ->add('title')
            ->add('token')
            ->add('buyer')
            ->add('city')
            ->add('exp_adress')
            ->add('exp_zip_code')
            ->add('exp_rel_id')
            ->add('exp_number')
            ->add('etiquette_url')
            ->add('tracing_url')
            ->add('status')
            ->add('user')
            ->add('category')
            ->add('Envoyer', SubmitType::class, ['attr' => ['class' => "button"]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
