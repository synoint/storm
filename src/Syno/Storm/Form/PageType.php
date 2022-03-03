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
    private TranslatorInterface $translator;
    private Services\Question   $questionService;

    public function __construct(TranslatorInterface $translator, Services\Question $questionService)
    {
        $this->translator      = $translator;
        $this->questionService = $questionService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Document\Question $question */
        foreach ($options['questions'] as $question) {

            $answerMap = $options['answers'][$question->getQuestionId()] ?? null;

            switch ($question->getQuestionTypeId()) {
                case Document\Question::TYPE_SINGLE_CHOICE:
                    $this->addSingleChoice($builder, $question, $answerMap);
                    $this->addFreeText($builder, $question, $answerMap);
                    break;
                case Document\Question::TYPE_MULTIPLE_CHOICE:
                    $this->addMultipleChoice($builder, $question, $answerMap);
                    $this->addFreeText($builder, $question, $answerMap);
                    break;
                case Document\Question::TYPE_SINGLE_CHOICE_MATRIX:
                case Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                    $this->addMatrix($builder, $question, $answerMap);
                    break;
                case Document\Question::TYPE_TEXT:
                    $this->addText($builder, $question, $answerMap);
                    break;
                case Document\Question::TYPE_LINEAR_SCALE:
                    $this->addLinearScale($builder, $question, $answerMap);
                    break;
                case Document\Question::TYPE_LINEAR_SCALE_MATRIX:
                    $this->addLinearScaleMatrix($builder, $question, $answerMap);
                    break;
            }
        }

        $builder->add('next', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'questions' => null,
                'answers' => null,
                'validation_groups' => ['form_validation_only']
            ]
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     * @param array|null           $answerMap
     */
    private function addSingleChoice(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        $questionAnswerIds = $answerMap ? array_keys($answerMap) : null;

        $choices = [];
        $data = null;

        foreach($question->getAnswers() as $answer){
            $choices[$answer->getLabel()] = $answer->getCode();

            if ($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)) {
                $data = $answer->getCode();
            }
        }

        $options = [
            'choices' => $choices,
            'required' => $question->isRequired(),
            'data' => $data,
            'expanded' => !$question->containsSelectField(),
            'placeholder' => null,
            'attr' => ['class' => 'custom-control custom-radio custom-radio-filled'],
            'choice_attr' => function () {
                return ['class' => 'custom-control-input'];
            },
            'label_attr' => ['class' => 'custom-control-label']
        ];

        if ($question->isRequired()) {
            $options['constraints'] = [
                new NotBlank([
                    'message' => $this->translator->trans('error.one.option.required'),
                    'groups' => ['form_validation_only']
                ])
            ];
        }

        $builder->add($question->getCode(), ChoiceType::class, $options);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     * @param array|null           $answerMap
     */
    private function addMultipleChoice(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        $questionAnswerIds = $answerMap ? array_keys($answerMap) : null;

        $choices = [];
        $data    = [];

        $selectedAnswersIsExclusive = $this->questionService->isSelectedAnswersExclusive($question, $questionAnswerIds);

        foreach($question->getAnswers() as $answer){
            $choices[$answer->getLabel()] = $answer->getCode();

            if ($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)) {
                $data[] = $answer->getCode();
            }
        }

        $builder->add($question->getCode(), ChoiceType::class, [
                'choices'     => $choices,
                'required'    => $question->isRequired(),
                'placeholder' => null,
                'constraints' => $question->isRequired() ? [
                    new Count([
                        'min'        => 1,
                        'minMessage' => $this->translator->trans('error.at.least.one.option.required'),
                        'groups'     => ['form_validation_only']
                    ])
                ] : null,
                'expanded'    => true,
                'multiple'    => true,
                'data'        => $data,
                'attr'        => ['class' => 'custom-control custom-checkbox custom-checkbox-filled'],
                'choice_attr' => function ($answerId) use ($question, $selectedAnswersIsExclusive, $answerMap) {
                    $attr['row_attr'] = '';
                    $attr             = ['class' => 'custom-control-input form-check-input'];


                    if ($question->getAnswerByCode($answerId)->getIsExclusive()) {
                        $attr['class']    .= ' exclusive';
                        $attr['row_attr'] = 'exclusive';
                    }

                    if ($selectedAnswersIsExclusive && !in_array($answerId, $answerMap)) {
                        $attr['disabled'] = 'disabled';
                    }

                    return $attr;
                },
                'label_attr'  => ['class' => 'custom-control-label'],
            ]
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     * @param array|null           $answerMap
     */
    private function addFreeText(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        /** @var Document\Answer $answer */
        foreach ($question->getAnswers() as $answer) {
            if ($answer->getIsFreeText()) {
                $builder->add($question->getInputName($answer->getCode()), TextType::class, [
                        'attr' => ['class' => 'is-free-text-input'],
                        'required' => false,
                        'constraints' => [
                            new OtherFilled([
                                'answer'            => $answer,
                                'respondentAnswers' => $answerMap,
                                'groups'            => ['form_validation_only']
                            ])
                        ],
                        'data' => $answerMap[$answer->getAnswerId()] ?? null,
                    ]
                );
            }
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     * @param array|null           $answerMap
     */
    private function addMatrix(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        $questionAnswerIds = $answerMap ? (array) array_keys($answerMap) : null;

        foreach (array_keys($question->getRows()) as $key => $rowCode) {
            $data    = [];
            $choices = [];

            foreach (array_keys($question->getColumns()) as $columnCode) {
                $answer                             = $question->getMatrixAnswer($rowCode, $columnCode);
                $choices[$answer->getColumnLabel()] = $answer->getColumnCode();

                if ($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)) {
                    $data[] = $answer->getColumnCode();
                }
            }

            $multiple = (Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX === $question->getQuestionTypeId());

            if ($multiple) {
                $constraint = new Count([
                    'min' => 1,
                    'minMessage' => $this->translator->trans('error.at.least.one.option.in.each.row.required'),
                    'groups' => ['form_validation_only']
                ]);
            } else {
                $data       = reset($data);
                $constraint = new NotBlank([
                    'message' => $this->translator->trans('error.one.option.in.each.row.required'),
                    'groups' => ['form_validation_only']
                ]);
            }

            $builder->add($question->getInputName($rowCode), ChoiceType::class, [
                'choices' => $choices,
                'multiple' => $multiple,
                'expanded' => true,
                'data' => $data,
                'placeholder' => null,
                'required' => $question->isRequired(),
                'constraints' => $question->isRequired() ? [$constraint] : null,
                'choice_attr' => function () {
                    return ['class' => 'custom-control-input'];
                },
            ]);
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     * @param array|null           $answerMap
     */
    private function addText(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        /** @var Document\Answer $answer */
        foreach ($question->getAnswers() as $answer) {
            if ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXT) {
                $options = [
                    'attr' => ['class' => 'custom-control custom-text'],
                    'required' => $question->isRequired(),
                    'data' => $answerMap[$answer->getAnswerId()] ?? ''
                ];

                if ($question->isRequired()) {
                    $options['constraints'] = new NotBlank(
                        [
                            'message' => $this->translator->trans('error.cant.be.blank'),
                            'groups' => ['form_validation_only']
                        ]
                    );
                }
                $builder->add($question->getInputName($answer->getCode()), TextType::class, $options);

            } elseif ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXTAREA) {

                $options = [
                    'attr' => ['class' => 'custom-control custom-textarea'],
                    'required' => $question->isRequired(),
                    'data' => $answerMap[$answer->getAnswerId()] ?? '',
                ];

                if ($question->isRequired()) {
                    $options['constraints'] = new NotBlank(
                        [
                            'message' => $this->translator->trans('error.cant.be.blank'),
                            'groups' => ['form_validation_only']
                        ]
                    );
                }

                $builder->add($question->getInputName($answer->getCode()), TextareaType::class, $options);
            }
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     * @param array|null           $answerMap
     */
    private function addLinearScale(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        $data = null;
        $questionAnswerIds = !empty($answerMap) ? array_keys($answerMap) : null;

        foreach ($question->getAnswers() as $answer) {

            if ($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)) {
                $data = $answer;
            }
        }

        $builder->add($question->getInputName(), LinearScale::class, [
            'choices'     => $question->getAnswers(),
            'required'    => $question->isRequired(),
            'data'        => $data,
            'constraints' => $question->isRequired() ? [
                new NotBlank([
                    'message' => $this->translator->trans('error.one.option.required'),
                    'groups'  => ['form_validation_only']
                ])
            ] : null,
            'label'       => $question->getText()
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Document\Question    $question
     * @param array|null           $answerMap
     */
    private function addLinearScaleMatrix(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        $questionAnswerIds = $answerMap ? (array) array_keys($answerMap) : null;

        foreach ($question->getRows() as $rowCode => $row) {
            $data  = null;
            $array = new ArrayCollection();

            foreach (array_keys($question->getColumns()) as $columnCode) {
                $answer = $question->getMatrixAnswer($rowCode, $columnCode);
                $array->add($answer);

                if ($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)) {
                    $data = $answer;
                }
            }

            $builder->add($question->getInputName($rowCode), LinearScaleMatrix::class, [
                'choices' => $array,
                'data' => $data,
                'required' => $question->isRequired(),
                'constraints' => $question->isRequired() ? [
                    new NotBlank([
                        'message' => $this->translator->trans('error.one.option.in.each.row.required'),
                        'groups' => ['form_validation_only']
                    ])
                ] : null,
                'label' => $row
            ]);
        }
    }

    public function getBlockPrefix(): string
    {
        return "p";
    }
}
