<?php

namespace Syno\Storm\Twig;

use Symfony\Component\Form\FormView;
use Syno\Storm\Document\Question;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormExtension extends AbstractExtension
{

    public function getFilters(): array
    {
        return [
            new TwigFilter('shuffle_answers', [$this, 'shuffleAnswers']),
            new TwigFilter('shuffle_array', [$this, 'shuffleArray'])
        ];
    }

    public function shuffleAnswers(FormView $form, Question $question): FormView
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

    public function shuffleArray(array $array): array
    {
        $orig = array_flip($array);
        shuffle($array);
        foreach($array as $key=>$n) {
            $data[$n] = $orig[$n];
        }
        return array_flip($data);
    }

}
