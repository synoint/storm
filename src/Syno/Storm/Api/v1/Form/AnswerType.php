<?php

namespace Syno\Storm\Api\v1\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Syno\Storm\Document;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('answerId', IntegerType::class)
            ->add('code', TextType::class, ['required' => false])
            ->add('rowCode', TextType::class, ['required' => false])
            ->add('columnCode', TextType::class, ['required' => false])
            ->add('sortOrder', IntegerType::class)
            ->add('answerFieldTypeId', IntegerType::class)
            ->add('label', TextType::class, ['required' => false])
            ->add('rowLabel', TextType::class, ['required' => false])
            ->add('columnLabel', TextType::class, ['required' => false])
            ->add('translations', CollectionType::class, [
                'entry_type' => AnswerTranslationType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class'      => Document\Answer::class,
           'csrf_protection' => false
        ]);
    }
}
