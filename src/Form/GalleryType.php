<?php

namespace App\Form;

use App\Entity\Gallery;
use App\Entity\MediaObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Doctrine\ORM\EntityRepository;

class GalleryType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ) {
        $builder
            ->add('title')
            ->add('description')
            ->add('order')
            ->add(
                'main',
                EntityType::class,
                ['class' => MediaObject::class, 'required' => false]
            )
            ->add(
                'separator',
                EntityType::class,
                [
                    'class' => MediaObject::class, 'required' => false
                ]
            )
            ->add(
                'showCase',
                EntityType::class,
                [
                    'class' => MediaObject::class, 'required' => false
                ]
            )
            ->add(
                'medias',
                CollectionType::class,
                [
                    'entry_type' => MediaObjectType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => Gallery::class,
                'allow_extra_fields' => false,
                'csrf_protection'    => false,
            ]
        );
    }
}
