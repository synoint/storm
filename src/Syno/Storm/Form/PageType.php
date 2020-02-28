<?php

namespace Syno\Storm\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Syno\Storm\Document;
use Syno\Storm\Form\Type\LinearScale;
use Syno\Storm\Form\Type\LinearScaleMatrix;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Document\Question $question */
        foreach ($options['questions'] as $question) {
            switch ($question->getQuestionTypeId()) {
                case Document\Question::TYPE_SINGLE_CHOICE:
                    $this->addSingleChoice($builder, $question);
                    break;
                case Document\Question::TYPE_MULTIPLE_CHOICE:
                    $this->addMultipleChoice($builder, $question);
                    break;
                case Document\Question::TYPE_SINGLE_CHOICE_MATRIX:
                case Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                    $this->addMatrix($builder, $question);
                    break;
                case Document\Question::TYPE_TEXT:
                    $this->addText($builder, $question);
                    break;
                case Document\Question::TYPE_LINEAR_SCALE:
                    $this->addLinearScale($builder, $question);
                    break;
                case Document\Question::TYPE_LINEAR_SCALE_MATRIX:
                    $this->addLinearScaleMatrix($builder, $question);
                    break;
            }
        }

        $builder->add('next', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['questions' => null]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addSingleChoice(FormBuilderInterface $builder, Document\Question $question)
    {
        $builder->add($question->getInputName(), ChoiceType::class, [
            'choices'  => $question->getChoices(),
            'required' => $question->isRequired(),
            'label'    => $question->getText(),
            'expanded' => !$question->containsSelectField(),
            'attr' => ['class' => 'custom-control custom-radio custom-radio-filled'],
            'choice_attr' => function() {
                return ['class' => 'custom-control-input'];
            },
            'label_attr' => ['class' => 'custom-control-label']
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addMultipleChoice(FormBuilderInterface $builder, Document\Question $question)
    {
        $builder->add($question->getInputName(), ChoiceType::class, [
            'choices'  => $question->getChoices(),
            'required' => $question->isRequired(),
            'expanded' => true,
            'multiple' => true,
            'attr' => ['class' => 'custom-control custom-checkbox custom-checkbox-filled'],
            'choice_attr' => function() {
                return ['class' => 'custom-control-input'];
            },
            'label_attr' => ['class' => 'custom-control-label']
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addMatrix(FormBuilderInterface $builder, Document\Question $question)
    {
        foreach (array_keys($question->getRows()) as $rowCode) {
            $choices = [];
            foreach (array_keys($question->getColumns()) as $columnCode) {
                $answer = $question->getMatrixAnswer($rowCode, $columnCode);
                $choices[$answer->getColumnLabel()] = $answer->getAnswerId();
            }

            $multiple = (Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX === $question->getQuestionTypeId());

            $builder->add($question->getInputName().$rowCode, ChoiceType::class, [
                'choices' => $choices,
                'multiple' => $multiple,
                'expanded' => true,
                'required' => $question->isRequired(),
                'choice_attr' => function() {
                    return ['class' => 'custom-control-input'];
                },
            ]);
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addText(FormBuilderInterface $builder, Document\Question $question)
    {
        /** @var Document\Answer $answer */
        foreach ($question->getAnswers() as $answer) {
            if ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXT) {
                $builder->add($answer->getAnswerId(), TextType::class, [
                    'attr' => ['class' => 'custom-control custom-text'],
                    'required' => $question->isRequired()
                ]);
            } elseif ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXTAREA) {
                $builder->add($answer->getAnswerId(), TextareaType::class, [
                    'attr' => ['class' => 'custom-control custom-textarea'],
                    'required' => $question->isRequired()
                ]);
            }
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addLinearScale(FormBuilderInterface $builder, Document\Question $question)
    {
        $builder->add($question->getInputName(), LinearScale::class, [
            'choices'  => $question->getAnswers(),
            'required' => $question->isRequired(),
            'label'    => $question->getText()
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addLinearScaleMatrix(FormBuilderInterface $builder, Document\Question $question)
    {

        foreach ($question->getRows() as $rowCode => $row) {
            $choices = [];
            $array = new ArrayCollection();
            foreach (array_keys($question->getColumns()) as $columnCode) {
                $answer = $question->getMatrixAnswer($rowCode, $columnCode);
                $array->add($answer);
                $choices[$answer->getColumnLabel()] = $answer->getAnswerId();
            }

            $builder->add($question->getInputName().$rowCode, LinearScaleMatrix::class, [
                'choices' => $array,
                'required' => $question->isRequired(),
                'label'    => $row
            ]);
        }
    }
}