<?php

namespace Syno\Storm\Api\v1\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Syno\Storm\Document;

class EndPageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('language', TextType::class, ['required' => true, 'empty_data' => ''])
            ->add('type', TextType::class, ['required' => true])
            ->add('content', TextType::class, ['required' => true]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => Document\EndPage::class,
                'csrf_protection' => false
            ]
        );
    }
}
