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
            ->add('config', ConfigType::class)
            ->add('hiddenValues', CollectionType::class, [
                'entry_type' => HiddenValueType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ]);
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
