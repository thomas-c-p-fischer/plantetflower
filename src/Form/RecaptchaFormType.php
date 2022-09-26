<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Test\FormBuilderInterface;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;

// Une classe "ReCaptchaType" disponible pour tous les formulaires.
// Elle peut être utilisée dans le FormBuilder comme un "TextType" ou "PasswordType"
class RecaptchaFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface|\Symfony\Component\Form\FormBuilderInterface $builder,array $options)
    {
        $builder->add("recaptcha", ReCaptchaType::class);
    }

}
