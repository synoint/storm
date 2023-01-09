<?php

namespace Syno\Storm\Twig;

use Symfony\Component\Form\FormView;
use Syno\Storm\Document\Answer;
use Syno\Storm\Document\Question;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FormExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getRandomAnswer', [$this, 'getRandomAnswer']),
        ];
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('shuffle_answers', [$this, 'shuffleAnswers']),
            new TwigFilter('shuffle_array', [$this, 'shuffleArray']),
        ];
    }

    public function getRandomAnswer(Question $question): Answer
    {
        $answersArray = $question->getAnswers()->toArray();
        shuffle($answersArray);

        return reset($answersArray);
    }

    public function shuffleAnswers(FormView $form, Question $question): FormView
    {
        $exclusives      = [];
        $freeTextAnswers = [];
        foreach ($form->vars['form']->children as $index => $child) {
            $answer = $question->getAnswerByCode($child->vars['value']);
            if ($answer) {
                if ($answer->getIsExclusive()) {
                    $exclusives[] = $child;
                    unset($form->vars['form']->children[$index]);
                }
                if ($answer->getIsFreeText()) {
                    $freeTextAnswers[] = $child;
                    unset($form->vars['form']->children[$index]);
                }
            }
        }
        shuffle($form->vars['form']->children);
        foreach($freeTextAnswers as $freeTextAnswer) {
            $form->vars['form']->children[] = $freeTextAnswer;
        }
        // Exclusive should be last because exclusive is answer option 'none' which is always the last one.
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
