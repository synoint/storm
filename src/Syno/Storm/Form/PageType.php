<?php

namespace Syno\Storm\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Syno\Storm\Document;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stormMakerPageId', IntegerType::class)
            ->add('code', TextType::class)
            ->add('sortOrder', IntegerType::class)
            ->add('content', TextType::class, ['required' => false])
            ->add('questions', CollectionType::class, [
                'entry_type' => QuestionType::class
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class'      => Document\Page::class,
           'csrf_protection' => false
        ]);
    }
}
