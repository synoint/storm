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
use Syno\Storm\Services;

class PageType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    /** @var Services\Question */
    private $questionService;

    /**
     * @param TranslatorInterface   $translator
     * @param Services\Question     $questionService
     */
    public function __construct(
        TranslatorInterface $translator,
        Services\Question   $questionService
    )
    {
        $this->translator       = $translator;
        $this->questionService  = $questionService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $answers = $options['answers'];

        /** @var Document\Question $question */
        foreach ($options['questions'] as $question) {

            $questionAnswers = null;

            if(!empty($answers[$question->getQuestionId()])){
                $questionAnswers = $answers[$question->getQuestionId()];
            }

            switch ($question->getQuestionTypeId()) {
                case Document\Question::TYPE_SINGLE_CHOICE:

                    $this->addSingleChoice($builder, $question, $questionAnswers);
                    $this->addFreeText($builder, $question, $questionAnswers);
                    break;
                case Document\Question::TYPE_MULTIPLE_CHOICE:
                    $this->addMultipleChoice($builder, $question, $questionAnswers);
                    $this->addFreeText($builder, $question, $questionAnswers);
                    break;
                case Document\Question::TYPE_SINGLE_CHOICE_MATRIX:
                case Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                    $this->addMatrix($builder, $question, $questionAnswers);
                    break;
                case Document\Question::TYPE_TEXT:
                    $this->addText($builder, $question, $questionAnswers);
                    break;
                case Document\Question::TYPE_LINEAR_SCALE:
                    $this->addLinearScale($builder, $question, $questionAnswers);
                    break;
                case Document\Question::TYPE_LINEAR_SCALE_MATRIX:
                    $this->addLinearScaleMatrix($builder, $question, $questionAnswers);
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
                'answers'           => null,
                'validation_groups' => ['form_validation_only']
            ]
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addSingleChoice(FormBuilderInterface $builder, Document\Question $question, ?array $questionAnswers)
    {
        $questionAnswerIds = $questionAnswers ? array_keys($questionAnswers) : null;

        $builder->add($question->getInputName(), ChoiceType::class, [
            'choices'     => $question->getChoices(),
            'required'    => $question->isRequired(),
            'data'        => $questionAnswerIds ? reset($questionAnswerIds) : null,
            'expanded'    => !$question->containsSelectField(),
            'constraints' => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.one.option.required'), 'groups' => ['form_validation_only']]) : null],
            'attr'        => ['class' => 'custom-control custom-radio custom-radio-filled'],
            'choice_attr' => function () {
                return ['class' => 'custom-control-input'];
            },
            'label_attr'  => ['class' => 'custom-control-label']
        ]);
    }

    /**
     * @param FormBuilderInterface  $builder
     * @param Document\Question     $question
     * @param array                 $questionAnswers
     */
    private function addMultipleChoice(FormBuilderInterface $builder, Document\Question $question, ?array $questionAnswers)
    {
        $questionAnswerIds = $questionAnswers ? array_keys($questionAnswers) : null;

        $selectedAnswersIsExclusive = $this->questionService->isSelectedAnswersExclusive($question, $questionAnswerIds);

        $builder->add($question->getInputName(), ChoiceType::class, [
                'choices'     => $question->getChoices(),
                'required'    => $question->isRequired(),
                'constraints' => [$question->isRequired() ? new Count(['min' => 1, 'minMessage' => $this->translator->trans('error.at.leat.one.option.required'), 'groups' => ['form_validation_only']]) : null],
                'expanded'    => true,
                'multiple'    => true,
                'data'        => $questionAnswerIds,
                'attr'        => ['class' => 'custom-control custom-checkbox custom-checkbox-filled'],
                'choice_attr' => function ($answerId) use ($question, $selectedAnswersIsExclusive, $questionAnswers) {

                    $attr = ['class' => 'custom-control-input form-check-input'];

                    if ($question->getAnswer($answerId)->getIsExclusive()) {
                        $attr['class'] .= ' exclusive';
                    }

                    if ($selectedAnswersIsExclusive && !in_array($answerId, $questionAnswers)) {
                        $attr['disabled'] = 'disabled';
                    }

                    return $attr;
                },
                'label_attr'  => ['class' => 'custom-control-label']
        ]
        );
    }

    /**
     * @param FormBuilderInterface  $builder
     * @param Document\Question     $question
     * @param array                 $questionAnswers
     */
    private function addFreeText(FormBuilderInterface $builder, Document\Question $question, ?array $questionAnswers)
    {
        /** @var Document\Answer $answer */
        foreach ($question->getAnswers() as $answer) {
            if ($answer->getIsFreeText()) {
                $builder->add($question->getInputName($answer->getAnswerId()), TextType::class, [
                        'attr'     => ['class' => 'is-free-text-input'],
                        'required' => false,
                        'data'     => isset($questionAnswers[$answer->getAnswerId()]) ? $questionAnswers[$answer->getAnswerId()] : null,
                    ]
                );
            }
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addMatrix(FormBuilderInterface $builder, Document\Question $question, $questionAnswers)
    {
        $questionAnswerIds = $questionAnswers ?(array) array_keys($questionAnswers) : null;

        foreach (array_keys($question->getRows()) as $key => $rowCode) {

            $data = [];

            $choices = [];
            foreach (array_keys($question->getColumns()) as $columnCode) {
                $answer = $question->getMatrixAnswer($rowCode, $columnCode);
                $choices[$answer->getColumnLabel()] = $answer->getAnswerId();

                 if($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)){
                     $data[] = $answer->getAnswerId();
                 }
            }

            $multiple = (Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX === $question->getQuestionTypeId());

            if ($multiple) {
                $constraint = new Count(['min' => 1, 'minMessage' => $this->translator->trans('error.at.least.one.option.in.each.row.required'), 'groups' => ['form_validation_only']]);
            } else {
                $data = reset($data);
                $constraint = new NotBlank(['message' => $this->translator->trans('error.one.option.in.each.row.required'), 'groups' => ['form_validation_only']]);
            }

            $builder->add($question->getInputName($rowCode), ChoiceType::class, [
                'choices'     => $choices,
                'multiple'    => $multiple,
                'expanded'    => true,
                'data'        => $data,
                'required'    => $question->isRequired(),
                'constraints' => [$question->isRequired() ? $constraint : null],
                'choice_attr' => function () {
                    return ['class' => 'custom-control-input'];
                },
            ]);
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addText(FormBuilderInterface $builder, Document\Question $question, $questionAnswers)
    {
        /** @var Document\Answer $answer */
        foreach ($question->getAnswers() as $answer) {
            if ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXT) {
                $builder->add($question->getInputName($answer->getAnswerId()), TextType::class, [
                    'attr'        => ['class' => 'custom-control custom-text'],
                    'required'    => $question->isRequired(),
                    'data'        => $questionAnswers[$answer->getAnswerId()],
                    'constraints' => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.cant.be.blank'), 'groups' => ['form_validation_only']]) : null],
                ]);
            } elseif ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXTAREA) {
                $builder->add($question->getInputName($answer->getAnswerId()), TextareaType::class, [
                    'attr'        => ['class' => 'custom-control custom-textarea'],
                    'required'    => $question->isRequired(),
                    'data'        => $questionAnswers[$answer->getAnswerId()],
                    'constraints' => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.cant.be.blank'), 'groups' => ['form_validation_only']]) : null]
                ]);
            }
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addLinearScale(FormBuilderInterface $builder, Document\Question $question, $questionAnswers)
    {
        $questionAnswerIds = !empty($questionAnswers) ? array_keys($questionAnswers) : null;

        $builder->add($question->getInputName(), LinearScale::class, [
            'choices'     => $question->getAnswers(),
            'required'    => $question->isRequired(),
            'data'        => $questionAnswerIds ? $question->getAnswer(reset($questionAnswerIds)) : null,
            'constraints' => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.one.option.required'), 'groups' => ['form_validation_only']]) : null],
            'label'       => $question->getText()
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addLinearScaleMatrix(FormBuilderInterface $builder, Document\Question $question, $questionAnswers)
    {
        $questionAnswerIds = $questionAnswers ?(array) array_keys($questionAnswers) : null;

        foreach ($question->getRows() as $rowCode => $row) {

            $data = null;
            $choices = [];
            $array = new ArrayCollection();
            foreach (array_keys($question->getColumns()) as $columnCode) {
                $answer = $question->getMatrixAnswer($rowCode, $columnCode);
                $array->add($answer);
                $choices[$answer->getColumnLabel()] = $answer->getAnswerId();

                if($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)){
                    $data = $answer;
                }
            }

            $builder->add($question->getInputName($rowCode), LinearScaleMatrix::class, [
                'choices'     => $array,
                'data'        => $data,
                'required'    => $question->isRequired(),
                'constraints' => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.one.option.in.each.row.required'), 'groups' => ['form_validation_only']]) : null],
                'label'       => $row
            ]);
        }
    }
}
