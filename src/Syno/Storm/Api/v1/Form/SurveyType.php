<?php

namespace Syno\Storm\Api\v1\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Syno\Storm\Document;

class SurveyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('surveyId', IntegerType::class)
            ->add('version', IntegerType::class)
            ->add('pages', CollectionType::class, [
                'entry_type' => PageType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('css', CollectionType::class, [
                'entry_type' => CssType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('config', ConfigType::class)
            ->add('parameters', CollectionType::class, [
                'entry_type' => ParameterType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('urls', CollectionType::class, [
                'entry_type' => UrlType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('languages', CollectionType::class, [
                'entry_type' => LanguageType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => SurveyTranslationType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('randomization', CollectionType::class, [
                'entry_type' => RandomizationType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('surveyCompleteCondition', SurveyCompleteConditionType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => Document\Survey::class,
                'csrf_protection' => false,
            ]
        );
    }
}
