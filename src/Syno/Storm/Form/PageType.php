<?php

namespace Syno\Storm\Form;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Syno\Storm\Document;
use Syno\Storm\Form\Type\MultipleChoice;
use Syno\Storm\Form\Type\SingleChoiceRadio;
use Syno\Storm\Form\Type\SingleChoiceSelect;

class PageType extends AbstractType
{
    const PREFIX = 'q_';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Document\Page $page */
        $page = $options['page'];

        /** @var Document\Question $question */
        foreach ($page->getQuestions() as $question) {

            switch ($question->getQuestionTypeId()) {

                case Document\Question::TYPE_SINGLE_CHOICE:

                    $builder->add(self::PREFIX . $question->getCode(), ChoiceType::class, [
                        'choices'  => $question->getChoices(),
                        'required' => $question->isRequired(),
                        'expanded' => !$this->displayInSelect($question->getAnswers())
                    ]);

                    break;
                case Document\Question::TYPE_MULTIPLE_CHOICE:
                    $builder->add(self::PREFIX . $question->getCode(), ChoiceType::class, [
                        'choices'  => $question->getChoices(),
                        'required' => $question->isRequired(),
                        'expanded' => true,
                        'multiple' => true
                    ]);
                    break;

                case Document\Question::TYPE_SINGLE_CHOICE_MATRIX:
                    foreach ($question->getRows() as $rowCode => $rowLabel) {

                        $choices = [];
                        foreach ($question->getColumns() as $columnCode => $columnLabel) {
                            $choices[$columnLabel] = $columnCode;
                        }

                        $builder->add(self::PREFIX . $rowCode, ChoiceType::class, [
                            'choices' => $choices,
                            'multiple' => false,
                            'expanded' => true
                        ]);
                    }
                    break;
                case Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                    foreach ($question->getRows() as $rowCode => $rowLabel) {

                        $choices = [];
                        foreach ($question->getColumns() as $columnCode => $columnLabel) {
                            $choices[$columnLabel] = $columnCode;
                        }

                        $builder->add(self::PREFIX . $rowCode, ChoiceType::class, [
                            'choices' => $choices,
                            'multiple' => true,
                            'expanded' => true
                        ]);
                    }
                    break;
                case Document\Question::TYPE_TEXT:
                    /** @var Document\Answer $answer */
                    foreach ($question->getAnswers() as $answer) {
                        if ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXT) {
                            $builder->add(self::PREFIX . $question->getCode(), TextType::class, [
                                'required' => $question->isRequired()
                            ]);
                        } elseif ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXTAREA) {
                            $builder->add(self::PREFIX . $question->getCode(), TextareaType::class, [
                                'required' => $question->isRequired()
                            ]);
                        }
                    }

                    break;
            }
        }

        $builder->add('next', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'page' => null
            ]
        );
    }

    private function displayInSelect(Collection $answers)
    {
        /** @var Document\Answer $answer */
        $answer = $answers->first();

        return $answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_SELECT;
    }
}
