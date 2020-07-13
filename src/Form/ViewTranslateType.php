<?php

namespace App\Form;

use App\Entity\ViewTranslate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ViewTranslateType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ) {
        $builder
            ->add('translate')
            ->add('key');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => ViewTranslate::class,
                'allow_extra_fields' => false,
                'csrf_protection'    => false,
            ]
        );
    }
}
