<?php

namespace Syno\Storm\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Session;
use Syno\Storm\Document;

class GaborGranger extends AbstractType
{
    private Session\SessionInterface $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'first_answer' => '',
                'question'     => null,
                'answerMap'    => null
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $question           = $options['question'];
        $randomFirstAnswers = $this->session->get('gabor-granger');

        if(!isset($randomFirstAnswers[$question->getQuestionId()])){
            $answers = $question->getAnswers()->filter(function(Document\Answer $answer){
                return $answer->getCode() != 0;
            })->toArray();

            shuffle($answers);
            $randomFirstAnswer = reset($answers);

            if($randomFirstAnswers){
                $randomFirstAnswers[$question->getQuestionId()] = $randomFirstAnswer;
            } else {
                $randomFirstAnswers = [$question->getQuestionId() => $randomFirstAnswer];
            }

            $this->session->set('gabor-granger', $randomFirstAnswers);
        } else {
            $randomFirstAnswer = $randomFirstAnswers[$question->getQuestionId()];
        }

        $view->vars['first_answer'] = $randomFirstAnswer;
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
