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
            new TwigFilter('shuffleFormChildren', [$this, 'shuffleFormChildren'])
        ];
    }

    /**
     * @param FormView $form
     * @param Question $question
     *
     * @return FormView
     */
    public function shuffleFormChildren(FormView $form, Question $question)
    {
        if ($question->getRandomizeAnswers()) {
            shuffle($form->vars['form']->children);

            return $form;
        } else {
            return $form;
        }
    }
}
