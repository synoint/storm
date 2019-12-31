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
                            'required' => $question->isRequired()
                        ]);
                    } else {
                        $builder->add('q_' . $question->getId(), SingleChoiceRadio::class, [
                            'choices'  => $question->getChoices(),
                            'required' => $question->isRequired()
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
                case Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                    break;
                case Document\Question::TYPE_TEXT:
                    /** @var Document\Answer $answer */
                    foreach ($question->getAnswers() as $answer) {
                        if ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXT) {
                            $builder->add('q_' . $question->getId(), TextType::class, [
                                'required' => $question->isRequired()
                            ]);
                        } elseif ($answer->getAnswerFieldTypeId() === Document\Answer::FIELD_TYPE_TEXTAREA) {
                            $builder->add('q_' . $question->getId(), TextareaType::class, [
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
