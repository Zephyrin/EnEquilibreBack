<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\MediaObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class ContactType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ) {
        $builder
            ->add('contact')
            ->add('comment')
            ->add('email')
            ->add(
                'background',
                EntityType::class,
                ['class' => MediaObject::class, 'required' => false]
            )
            ->add(
                'separator',
                EntityType::class,
                [
                    'class' => MediaObject::class, 'required' => false
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => Contact::class,
                'allow_extra_fields' => false,
                'csrf_protection'    => false,
            ]
        );
    }
}
