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
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;
use Syno\Storm\Document;
use Syno\Storm\Form\Type\LinearScale;
use Syno\Storm\Form\Type\LinearScaleMatrix;

class PageType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Document\Question $question */
        foreach ($options['questions'] as $question) {
            switch ($question->getQuestionTypeId()) {
                case Document\Question::TYPE_SINGLE_CHOICE:
                    $this->addSingleChoice($builder, $question);
                    $this->addFreeText($builder, $question);
                    break;
                case Document\Question::TYPE_MULTIPLE_CHOICE:
                    $this->addMultipleChoice($builder, $question);
                    $this->addFreeText($builder, $question);
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
        $resolver->setDefaults(
            [
                'questions'         => null,
                'validation_groups' => ['form_validation_only']
            ]
        );
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
            'expanded' => !$question->containsSelectField(),
            'constraints'  => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.one.option.required'), 'groups' => ['form_validation_only']]) : null],
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
            'constraints'  => [$question->isRequired() ? new Count(['min' => 1, 'minMessage' => $this->translator->trans('error.at.leat.one.option.required'), 'groups' => ['form_validation_only']]) : null],
            'expanded' => true,
            'multiple' => true,
            'attr'        => ['class' => 'custom-control custom-checkbox custom-checkbox-filled'],
            'choice_attr' => function ($answerId) use ($question) {
            if ($question->isAnswerExclusive($answerId)) {
                    return ['class' => 'custom-control-input exclusive'];
                }
                return ['class' => 'custom-control-input'];
            },
            'label_attr'  => ['class' => 'custom-control-label']
        ]
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addFreeText(FormBuilderInterface $builder, Document\Question $question)
    {
        /** @var Document\Answer $answer */
        foreach ($question->getAnswers() as $answer) {
            if ($answer->getIsFreeText()) {
                $builder->add($question->getInputName($answer->getAnswerId()), TextType::class, [
                    'attr' => ['class' => 'is-free-text-input'], 'required' => false]);
            }
        }
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

            if ($multiple) {
                $constraint = new Count(['min' => 1, 'minMessage' => $this->translator->trans('error.at.least.one.option.in.each.row.required'), 'groups' => ['form_validation_only']]);
            } else {
                $constraint = new NotBlank(['message' => $this->translator->trans('error.one.option.in.each.row.required'), 'groups' => ['form_validation_only']]);
            }

            $builder->add($question->getInputName($rowCode), ChoiceType::class, [
                'choices'  => $choices,
                'multiple' => $multiple,
                'expanded' => true,
                'required' => $question->isRequired(),
                'constraints'  => [$question->isRequired() ? $constraint : null],
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
                $builder->add($question->getInputName($answer->getAnswerId()), TextType::class, [
                    'attr'         => ['class' => 'custom-control custom-text'],
                    'required'     => $question->isRequired(),
                    'constraints'  => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.cant.be.blank'), 'groups' => ['form_validation_only']]) : null],
                ]);
            } elseif ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXTAREA) {
                $builder->add($question->getInputName($answer->getAnswerId()), TextareaType::class, [
                    'attr'        => ['class' => 'custom-control custom-textarea'],
                    'required'    => $question->isRequired(),
                    'constraints' => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.cant.be.blank'), 'groups' => ['form_validation_only']]) : null]
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
            'choices'     => $question->getAnswers(),
            'required'    => $question->isRequired(),
            'constraints' => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.one.option.required'), 'groups' => ['form_validation_only']]) : null],
            'label'       => $question->getText()
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

            $builder->add($question->getInputName($rowCode), LinearScaleMatrix::class, [
                'choices'     => $array,
                'required'    => $question->isRequired(),
                'constraints' => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.one.option.in.each.row.required'), 'groups' => ['form_validation_only']]) : null],
                'label'       => $row
            ]);
        }
    }
}
