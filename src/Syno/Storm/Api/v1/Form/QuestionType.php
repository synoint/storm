<?php

namespace Syno\Storm\Api\v1\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->add('stormMakerQuestionId', IntegerType::class)
            ->add('code', TextType::class)
            ->add('sortOrder', IntegerType::class)
            ->add('required', HiddenType::class)
            ->add('text', TextType::class)
            ->add('questionTypeId', IntegerType::class)

            ->add('answers', CollectionType::class, [
                'entry_type' => AnswerType::class,
                'by_reference'  => false,
                'allow_add'     => true
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class'      => Document\Question::class,
           'csrf_protection' => false
        ]);
    }
}