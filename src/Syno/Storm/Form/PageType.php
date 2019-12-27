<?php

namespace Syno\Storm\Form;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Document\Page $page */
        $page = $options['page'];

        /** @var Document\Question $question */
        foreach ($page->getQuestions() as $question) {

            switch ($question->getQuestionTypeId()) {

                case Document\Question::TYPE_SINGLE_CHOICE:
                    if ($this->displayInSelect($question->getAnswers())) {
                        $builder->add('q_' . $question->getId(), SingleChoiceSelect::class, [
                            'choices'  => $question->getChoices(),
                            'required' => $question->isRequired(),
//                            'block_name' => 'single_choice_select',
                        ]);
                    } else {
                        $builder->add('q_' . $question->getId(), SingleChoiceRadio::class, [
                            'choices'  => $question->getChoices(),
                            'required' => $question->isRequired(),
//                            'block_prefix' => 'single_choice_radio',
                        ]);
                    }
                    break;
                case Document\Question::TYPE_MULTIPLE_CHOICE:
                    $builder->add('q_' . $question->getId(), MultipleChoice::class, [
                        'choices'  => $question->getChoices(),
                        'required' => $question->isRequired()
                    ]);
                    break;

                case Document\Question::TYPE_SINGLE_CHOICE_MATRIX:
                    /** @var Document\Answer $answer */
                    foreach ($question->getAnswers() as $answer) {
                        if ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_RADIO) {
                            $builder->add('q_' . $question->getId() . '_' . $answer->getRowCode(), RadioType::class, [
                                'value' => $answer->getId()
                            ]);
                        }
                    }
                    break;

                case Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                    /** @var Document\Answer $answer */
                    foreach ($question->getAnswers() as $answer) {
                        if ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_CHECKBOX) {
                            $builder->add('q_' . $question->getId() . '_' . $answer->getRowCode(), CheckboxType::class, [
                                'value' => $answer->getId()
                            ]);
                        }
                    }
                    break;
                case Document\Question::TYPE_TEXT:
                    /** @var Document\Answer $answer */
                    foreach ($question->getAnswers() as $answer) {
                        if ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXT) {
                            $builder->add('a_' . $question->getId(), TextType::class);
                        } elseif ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXTAREA) {
                            $builder->add('a_' . $question->getId(), TextareaType::class);
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
