<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\MediaObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('subTitle')
            ->add('description')
            ->add('order')
            ->add(
                'image',
                EntityType::class,
                [
                    'class' => MediaObject::class, 'required' => false
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => Event::class,
            'allow_extra_fields' => false,
            'csrf_protection'    => false
        ]);
    }
}
