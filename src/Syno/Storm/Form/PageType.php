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
use Syno\Storm\Validator\Constraints\OtherFilled;

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
        /** @var Document\Question $question */
        foreach ($options['questions'] as $question) {

            $respondentAnswers = !empty($options['respondentAnswers'][$question->getQuestionId()]) ? $options['respondentAnswers'][$question->getQuestionId()] : null;

            switch ($question->getQuestionTypeId()) {
                case Document\Question::TYPE_SINGLE_CHOICE:

                    $this->addSingleChoice($builder, $question, $respondentAnswers);
                    $this->addFreeText($builder, $question, $respondentAnswers);
                    break;
                case Document\Question::TYPE_MULTIPLE_CHOICE:
                    $this->addMultipleChoice($builder, $question, $respondentAnswers);
                    $this->addFreeText($builder, $question, $respondentAnswers);
                    break;
                case Document\Question::TYPE_SINGLE_CHOICE_MATRIX:
                case Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                    $this->addMatrix($builder, $question, $respondentAnswers);
                    break;
                case Document\Question::TYPE_TEXT:
                    $this->addText($builder, $question, $respondentAnswers);
                    break;
                case Document\Question::TYPE_LINEAR_SCALE:
                    $this->addLinearScale($builder, $question, $respondentAnswers);
                    break;
                case Document\Question::TYPE_LINEAR_SCALE_MATRIX:
                    $this->addLinearScaleMatrix($builder, $question, $respondentAnswers);
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
                'respondentAnswers' => null,
                'validation_groups' => ['form_validation_only']
            ]
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addSingleChoice(FormBuilderInterface $builder, Document\Question $question, ?array $respondentAnswers)
    {
        $questionAnswerIds = $respondentAnswers ? array_keys($respondentAnswers) : null;

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
     * @param array                 $respondentAnswers
     */
    private function addMultipleChoice(FormBuilderInterface $builder, Document\Question $question, ?array $respondentAnswers)
    {
        $questionAnswerIds = $respondentAnswers ? array_keys($respondentAnswers) : null;

        $selectedAnswersIsExclusive = $this->questionService->isSelectedAnswersExclusive($question, $questionAnswerIds);

        $builder->add($question->getInputName(), ChoiceType::class, [
                'choices'     => $question->getChoices(),
                'required'    => $question->isRequired(),
                'constraints' => [$question->isRequired() ? new Count(['min' => 1, 'minMessage' => $this->translator->trans('error.at.leat.one.option.required'), 'groups' => ['form_validation_only']]) : null],
                'expanded'    => true,
                'multiple'    => true,
                'data'        => $questionAnswerIds,
                'attr'        => ['class' => 'custom-control custom-checkbox custom-checkbox-filled'],
                'choice_attr' => function ($answerId) use ($question, $selectedAnswersIsExclusive, $respondentAnswers) {

                    $attr = ['class' => 'custom-control-input form-check-input'];

                    if ($question->getAnswer($answerId)->getIsExclusive()) {
                        $attr['class'] .= ' exclusive';
                    }

                    if ($selectedAnswersIsExclusive && !in_array($answerId, $respondentAnswers)) {
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
     * @param array                 $respondentAnswers
     */
    private function addFreeText(FormBuilderInterface $builder, Document\Question $question, ?array $respondentAnswers)
    {
        /** @var Document\Answer $answer */
        foreach ($question->getAnswers() as $answer) {
            if ($answer->getIsFreeText()) {
                $builder->add($question->getInputName($answer->getAnswerId()), TextType::class, [
                        'attr'     => ['class' => 'is-free-text-input'],
                        'required' => false,
                        'constraints' => [
                            new OtherFilled(['answer' => $answer, 'respondentAnswers' => $respondentAnswers,  'groups' => ['form_validation_only']])
                        ],
                        'data'     => isset($respondentAnswers[$answer->getAnswerId()]) ? $respondentAnswers[$answer->getAnswerId()] : null,
                    ]
                );
            }
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addMatrix(FormBuilderInterface $builder, Document\Question $question, $respondentAnswers)
    {
        $questionAnswerIds = $respondentAnswers ?(array) array_keys($respondentAnswers) : null;

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
    private function addText(FormBuilderInterface $builder, Document\Question $question, $respondentAnswers)
    {
        /** @var Document\Answer $answer */
        foreach ($question->getAnswers() as $answer) {
            if ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXT) {
                $builder->add($question->getInputName($answer->getAnswerId()), TextType::class, [
                    'attr'        => ['class' => 'custom-control custom-text'],
                    'required'    => $question->isRequired(),
                    'data'        => $respondentAnswers[$answer->getAnswerId()],
                    'constraints' => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.cant.be.blank'), 'groups' => ['form_validation_only']]) : null],
                ]);
            } elseif ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXTAREA) {
                $builder->add($question->getInputName($answer->getAnswerId()), TextareaType::class, [
                    'attr'        => ['class' => 'custom-control custom-textarea'],
                    'required'    => $question->isRequired(),
                    'data'        => $respondentAnswers[$answer->getAnswerId()],
                    'constraints' => [$question->isRequired() ? new NotBlank(['message' => $this->translator->trans('error.cant.be.blank'), 'groups' => ['form_validation_only']]) : null]
                ]);
            }
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     */
    private function addLinearScale(FormBuilderInterface $builder, Document\Question $question, $respondentAnswers)
    {
        $questionAnswerIds = !empty($respondentAnswers) ? array_keys($respondentAnswers) : null;

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
    private function addLinearScaleMatrix(FormBuilderInterface $builder, Document\Question $question, $respondentAnswers)
    {
        $questionAnswerIds = $respondentAnswers ?(array) array_keys($respondentAnswers) : null;

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
