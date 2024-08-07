<?php

namespace Syno\Storm\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;
use Syno\Storm\Document;
use Syno\Storm\Form\Type\LinearScale;
use Syno\Storm\Form\Type\LinearScaleMatrix;
use Syno\Storm\Form\Type\GaborGranger;
use Syno\Storm\Services;
use Syno\Storm\Validator\Constraints\OtherFilled;
use Symfony\Component\HttpFoundation\Session;

class PageType extends AbstractType
{
    private TranslatorInterface $translator;
    private Services\Question   $questionService;

    public function __construct(
        TranslatorInterface $translator,
        Services\Question $questionService
    ) {
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
                case Document\Question::TYPE_GABOR_GRANGER:
                    $this->addGaborGranger($builder, $question, $answerMap);
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
                case Document\Question::TYPE_MULTI_TEXT:
                    $this->addMultiText($builder, $question, $answerMap);
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
                'questions'         => null,
                'answers'           => null,
                'validation_groups' => ['form_validation_only']
            ]
        );
    }

    private function addSingleChoice(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        $questionAnswerIds = $answerMap ? array_keys($answerMap) : null;

        $labels = [];
        $codes  = [];
        $data   = null;

        foreach ($question->getAnswers() as $answer) {
            $labels[$answer->getAnswerId()] = $answer->getLabel();
            $codes[$answer->getAnswerId()]  = $answer->getCode();

            if ($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)) {
                $data = $answer->getCode();
            }
        }

        $options = [
            'choices'     => $codes,
            'required'    => $question->isRequired(),
            'data'        => $data,
            'expanded'    => !$question->containsSelectField(),
            'placeholder' => null,
            'attr'        => ['class' => 'custom-control custom-radio custom-radio-filled'],
            'choice_label' => function ($choice, $id) use ($labels) {
                return $labels[$id];
            },
            'choice_attr' => function () {
                return ['class' => 'custom-control-input'];
            },
            'label_attr'  => ['class' => 'custom-control-label']
        ];

        if ($question->isRequired()) {
            $options['constraints'] = [
                new NotBlank([
                    'message' => $this->translator->trans('error.one.option.required'),
                    'groups'  => ['form_validation_only']
                ])
            ];
        }

        $builder->add($question->getCode(), ChoiceType::class, $options);
    }

    public function addGaborGranger(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        $questionAnswerIds = $answerMap ? array_keys($answerMap) : null;

        $choices       = [];
        $choiceAnswers = [];
        $data          = null;

        foreach ($question->getAnswers() as $key => $answer) {

            $answer->first                     = $key == 0;
            $choices[$answer->getLabel()]      = $answer->getCode();
            $choiceAnswers[$answer->getCode()] = $answer;

            if ($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)) {
                $data = $answer->getCode();
            }
        }

        $options = [
            'choices'     => $choices,
            'question'    => $question,
            'required'    => $question->isRequired(),
            'data'        => $data,
            'expanded'    => !$question->containsSelectField(),
            'choice_attr' => function ($choice) use ($choiceAnswers) {
                return
                    [
                        'data-label' => $choiceAnswers[$choice]->getLabel(),
                        'class'      => $choiceAnswers[$choice]->first ? 'zero__input' : ''
                    ];
            },
            'label_attr'  => ['class' => 'custom-control-label']
        ];

        if ($question->isRequired()) {
            $options['constraints'] = [
                new NotBlank([
                    'message' => $this->translator->trans('error.answer.required'),
                    'groups'  => ['form_validation_only']
                ])
            ];
        }

        $builder->add($question->getCode(), GaborGranger::class, $options);
    }

    private function addMultipleChoice(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        $questionAnswerIds = $answerMap ? array_keys($answerMap) : null;

        $labels = [];
        $codes  = [];
        $data   = [];

        $selectedAnswersIsExclusive = $this->questionService->isSelectedAnswersExclusive($question, $questionAnswerIds);

        foreach ($question->getAnswers() as $answer) {
            $labels[$answer->getAnswerId()] = $answer->getLabel();
            $codes[$answer->getAnswerId()]  = $answer->getCode();

            if ($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)) {
                $data[] = $answer->getCode();
            }
        }

        $options = [
            'choices'      => $codes,
            'required'     => $question->isRequired(),
            'placeholder'  => null,
            'expanded'     => true,
            'multiple'     => true,
            'data'         => $data,
            'attr'         => ['class' => 'custom-control custom-checkbox custom-checkbox-filled'],
            'choice_label' => function ($choice, $id) use ($labels) {
                return $labels[$id];
            },
            'choice_attr'  => function ($answerCode, $answerId) use ($question, $selectedAnswersIsExclusive, $answerMap) {
                $attr['row_attr'] = '';
                $attr             = ['class' => 'custom-control-input form-check-input'];

                if ($question->getAnswerByCode($answerCode)->getIsExclusive()) {
                    $attr['class']    .= ' exclusive';
                    $attr['row_attr'] = 'exclusive';
                }
                if ($selectedAnswersIsExclusive && !key_exists($answerId, $answerMap)) {
                    $attr['disabled'] = 'disabled';
                }

                return $attr;
            },
            'label_attr'   => ['class' => 'custom-control-label']
        ];

        if ($question->isRequired()) {
            $options['constraints'] = [
                new Count([
                    'min'        => 1,
                    'minMessage' => $this->translator->trans('error.at.least.one.option.required'),
                    'groups'     => ['form_validation_only']
                ])
            ];
        }

        $builder->add($question->getCode(), ChoiceType::class, $options);
    }

    private function addFreeText(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        /** @var Document\Answer $answer */
        foreach ($question->getAnswers() as $answer) {
            if ($answer->getIsFreeText()) {
                $builder->add($question->getInputName($answer->getCode()), TextType::class, [
                        'attr'        => ['class' => 'is-free-text-input'],
                        'required'    => false,
                        'constraints' => [
                            new OtherFilled([
                                'answer'            => $answer,
                                'respondentAnswers' => $answerMap,
                                'groups'            => ['form_validation_only']
                            ])
                        ],
                        'data'        => $answerMap[$answer->getAnswerId()] ?? null,
                    ]
                );
            }
        }
    }

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
                    'min'        => 1,
                    'minMessage' => $this->translator->trans('error.at.least.one.option.in.each.row.required'),
                    'groups'     => ['form_validation_only']
                ]);
            } else {
                $data       = reset($data);
                $constraint = new NotBlank([
                    'message' => $this->translator->trans('error.one.option.in.each.row.required'),
                    'groups'  => ['form_validation_only']
                ]);
            }

            $options = [
                'choices'     => $choices,
                'multiple'    => $multiple,
                'expanded'    => true,
                'data'        => $data !== false ? $data : null,
                'placeholder' => null,
                'label_html'  => true,
                'required'    => $question->isRequired(),
                'choice_attr' => function () {
                    return ['class' => 'custom-control-input'];
                },
            ];

            if ($question->isRequired()) {
                $options['constraints'] = [$constraint];
            }

            $builder->add($question->getInputName($rowCode), ChoiceType::class, $options);
        }
    }

    private function addText(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        /** @var Document\Answer $answer */
        $answer = $question->getAnswers()->first();

        $options['required'] = $question->isRequired();
        $options['data']     = $answerMap[$answer->getAnswerId()] ?? '';

        if ($question->isRequired()) {
            $options['constraints'][] = new NotBlank(
                [
                    'message' => $this->translator->trans('error.cant.be.blank'),
                    'groups'  => ['form_validation_only']
                ]
            );
        }

        switch ($answer->getAnswerFieldTypeId()) {
            case Document\Answer::FIELD_TYPE_TEXTAREA:
                $options['attr'] = ['class' => 'custom-control custom-textarea'];

                $builder->add($question->getInputName($answer->getCode()), TextareaType::class, $options);
                break;

            case Document\Answer::FIELD_TYPE_PHONE:
                $options['attr'] = ['class' => 'custom-control custom-text'];

                $options['constraints'][] = new Regex(
                    [
                        'pattern' => '/^[+\d\s]{1,18}$/',
                        'groups' => ['form_validation_only']
                    ]
                );

                $builder->add($question->getInputName($answer->getCode()), TelType::class, $options);
                break;

            case Document\Answer::FIELD_TYPE_EMAIL:
                $options['attr']          = ['class' => 'custom-control custom-text'];
                $options['constraints'][] = new Email(['groups' => ['form_validation_only']]);

                $builder->add($question->getInputName($answer->getCode()), TextType::class, $options);
                break;
            case Document\Answer::FIELD_TYPE_TEXT:
            case Document\Answer::FIELD_TYPE_FIRST_LAST_NAME:
                $options['attr'] = ['class' => 'custom-control custom-text'];

                $builder->add($question->getInputName($answer->getCode()), TextType::class, $options);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown answer field type id: %d',
                    $answer->getAnswerFieldTypeId()));
        }
    }

    private function addMultiText(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        /** @var Document\Answer $answer */
        foreach ($question->getAnswers() as $answer) {
            $options = [];
            $options['required'] = $question->isRequired();
            $options['data']     = $answerMap[$answer->getAnswerId()] ?? '';
            $options['attr'] = ['class' => 'custom-control'];

            if ($question->isRequired()) {
                $options['constraints'][] = new NotBlank(
                    [
                        'message' => $this->translator->trans('error.cant.be.blank'),
                        'groups'  => ['form_validation_only']
                    ]
                );
            }

            switch ($answer->getAnswerFieldTypeId()) {
                case Document\Answer::FIELD_TYPE_TEXTAREA:
                    $builder->add($question->getInputName($answer->getCode()), TextareaType::class, $options);
                    break;

                case Document\Answer::FIELD_TYPE_PHONE:
                    $options['constraints'][] = new Regex(
                        [
                            'pattern' => '/^\+?[0-9][0-9]{7,14}$/',
                            'groups' => ['form_validation_only']
                        ]
                    );

                    $builder->add($question->getInputName($answer->getCode()), TelType::class, $options);
                    break;

                case Document\Answer::FIELD_TYPE_EMAIL:
                    $options['constraints'][] = new Email(['groups' => ['form_validation_only']]);

                    $builder->add($question->getInputName($answer->getCode()), TextType::class, $options);
                    break;
                case Document\Answer::FIELD_TYPE_TEXT:
                case Document\Answer::FIELD_TYPE_FIRST_LAST_NAME:
                case Document\Answer::FIELD_TYPE_OTHER:
                    $builder->add($question->getInputName($answer->getCode()), TextType::class, $options);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown answer field type id: %d',
                        $answer->getAnswerFieldTypeId()));
            }
        }
    }

    private function addLinearScale(FormBuilderInterface $builder, Document\Question $question, ?array $answerMap)
    {
        $data              = null;
        $questionAnswerIds = !empty($answerMap) ? array_keys($answerMap) : null;

        foreach ($question->getAnswers() as $answer) {

            if ($questionAnswerIds && in_array($answer->getAnswerId(), $questionAnswerIds)) {
                $data = $answer;
            }
        }

        $options = [
            'choices'  => $question->getAnswers(),
            'required' => $question->isRequired(),
            'data'     => $data,
            'label'    => $question->getText()
        ];

        if ($question->isRequired()) {
            $options['constraints'] = [
                new NotBlank([
                    'message' => $this->translator->trans('error.one.option.required'),
                    'groups'  => ['form_validation_only']
                ])
            ];
        }

        $builder->add($question->getInputName(), LinearScale::class, $options);
    }

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

            $options = [
                'choices'  => $array,
                'data'     => $data,
                'required' => $question->isRequired(),
                'label'    => $row
            ];

            if ($question->isRequired()) {
                $options['constraints'] = [
                    new NotBlank([
                        'message' => $this->translator->trans('error.one.option.in.each.row.required'),
                        'groups'  => ['form_validation_only']
                    ])
                ];
            }

            $builder->add($question->getInputName($rowCode), LinearScaleMatrix::class, $options);
        }
    }

    public function getBlockPrefix(): string
    {
        return "p";
    }
}
