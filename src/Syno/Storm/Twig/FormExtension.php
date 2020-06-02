<?php

namespace Syno\Storm\Twig;

use Symfony\Component\Form\FormView;
use Syno\Storm\Document\Question;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('shuffle_question_answers', [$this, 'shuffleQuestionAnswers'])
        ];
    }

    /**
     * @param FormView $form
     * @param Question $question
     *
     * @return FormView
     */
    public function shuffleQuestionAnswers(FormView $form, Question $question)
    {
        shuffle($form->vars['form']->children);
        $exclusives      = [];
        $freeTextAnswers = [];
        foreach ($form->vars['form']->children as $index => $child) {
            if ($question->getAnswer($child->vars['value'])->getIsExclusive()) {
                $exclusives[] = $child;
                unset($form->vars['form']->children[$index]);
            }
            if ($question->getAnswer($child->vars['value'])->getIsFreeText()) {
                $freeTextAnswers[] = $child;
                unset($form->vars['form']->children[$index]);
            }
        }
        // Exclusive should be last because exclusive is answer option 'none' which is always the last one.
        foreach($freeTextAnswers as $freeTextAnswer) {
            $form->vars['form']->children[] = $freeTextAnswer;
        }
        foreach($exclusives as $exclusive) {
            $form->vars['form']->children[] = $exclusive;
        }

        return $form;
    }
}
