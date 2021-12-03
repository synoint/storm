<?php

namespace Syno\Storm\Api\v1\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Syno\Storm\Document;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('questionId', IntegerType::class)
            ->add('code', TextType::class)
            ->add('sortOrder', IntegerType::class)
            ->add('required', CheckboxType::class)
            ->add('hidden', CheckboxType::class)
            ->add('text', TextType::class)
            ->add('randomizeAnswers', CheckboxType::class)
            ->add('randomizeRows', CheckboxType::class)
            ->add('randomizeColumns', CheckboxType::class)
            ->add('questionTypeId', IntegerType::class)
            ->add('scoreModuleId', IntegerType::class)
            ->add('scoreModuleParentId', IntegerType::class)

            ->add('answers', CollectionType::class, [
                'entry_type' => AnswerType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('showConditions', CollectionType::class, [
                'entry_type' => ShowConditionType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('jumpToConditions', CollectionType::class, [
                'entry_type' => JumpToConditionType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('screenoutConditions', CollectionType::class, [
                'entry_type' => ScreenoutConditionType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => QuestionTranslationType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class'      => Document\Question::class,
           'csrf_protection' => false
        ]);
    }
}
