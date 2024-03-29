<?php

namespace Syno\Storm\Api\v1\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Syno\Storm\Document;

class PagePreviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', TextType::class)
            ->add('logoPath', TextType::class)
            ->add('page', CollectionType::class, [
                'entry_type'   => PageType::class,
                'by_reference' => false,
                'allow_add'    => true
            ])
            ->add('css', CollectionType::class, [
                'entry_type'   => CssType::class,
                'by_reference' => false,
                'allow_add'    => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => Document\PagePreview::class,
                'csrf_protection' => false,
            ]
        );
    }
}
