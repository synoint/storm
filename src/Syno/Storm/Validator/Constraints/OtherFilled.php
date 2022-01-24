<?php
namespace Syno\Storm\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Syno\Storm\Document\Answer;
use Symfony\Component\Validator\Exception\MissingOptionsException;


class OtherFilled extends Constraint
{
    public string $message = 'error.selected.option.text.required';
    public Answer $answer;
    public ?array $respondentAnswers = [];

    public function __construct($options)
    {
        if (null === $options['answer']) {
            throw new MissingOptionsException(sprintf('Option "answer" must be given for constraint %s', __CLASS__), ['answer']);
        }

        $this->answer            = $options['answer'];
        $this->respondentAnswers = $options['respondentAnswers'];

        parent::__construct($options);
    }

    public function validatedBy(): string
    {
        return \get_class($this).'Validator';
    }

    public function getTargets(): string
    {
        return 'class';
    }
}
