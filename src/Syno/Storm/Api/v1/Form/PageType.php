<?php

namespace Syno\Storm\Api\v1\Form;

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
            ->add('pageId', IntegerType::class)
            ->add('surveyId', IntegerType::class)
            ->add('version', IntegerType::class)
            ->add('code', TextType::class)
            ->add('sortOrder', IntegerType::class)
            ->add('content', TextType::class, ['required' => false])
            ->add('javascript', TextType::class, ['required' => false])
            ->add('translations', CollectionType::class, [
                'entry_type'   => PageTranslationType::class,
                'by_reference' => false,
                'allow_add'    => true
            ])
            ->add('questions', CollectionType::class, [
                'entry_type'   => QuestionType::class,
                'by_reference' => false,
                'allow_add'    => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => Document\Page::class,
            'allow_extra_fields' => true,
            'csrf_protection'    => false
        ]);
    }
}
