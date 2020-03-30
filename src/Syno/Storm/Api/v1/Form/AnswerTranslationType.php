<?php

namespace Syno\Storm\Api\v1\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Syno\Storm\Document;

class AnswerTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', TextType::class)
            ->add('label', TextType::class, ['required' => false])
            ->add('rowLabel', TextType::class, ['required' => false])
            ->add('columnLabel', TextType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'      => Document\AnswerTranslation::class,
            'csrf_protection' => false
        ]);
    }
}
