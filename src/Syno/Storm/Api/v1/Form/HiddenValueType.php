<?php

namespace Syno\Storm\Api\v1\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Syno\Storm\Document;

class HiddenValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('code', TextType::class)
            ->add('urlParam', TextType::class)
            ->add('type', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class'      => Document\HiddenValue::class,
           'csrf_protection' => false
        ]);
    }
}
