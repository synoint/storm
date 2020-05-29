<?php
namespace Syno\Storm\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;


class OtherFilled extends Constraint
{
    public $message = 'error.selected.option.text.required';
    public $answer;
    public $respondentAnswers;

    public function __construct($options)
    {
        if (null === $options['answer']) {
            throw new MissingOptionsException(sprintf('Option "answer" must be given for constraint %s', __CLASS__), ['answer']);
        }

        $this->answer               = $options['answer'];
        $this->respondentAnswers    = $options['respondentAnswers'];

        parent::__construct($options);
    }

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }

    public function getTargets(){
        return 'class';
    }
}