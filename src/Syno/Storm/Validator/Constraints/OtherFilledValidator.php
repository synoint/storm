<?php
namespace Syno\Storm\Validator\Constraints;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OtherFilledValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $answerIsSelected = null;

        if($constraint->respondentAnswers){
            $respondentAnswerIds = array_keys($constraint->respondentAnswers);
            $answerIsSelected = in_array($constraint->answer->getAnswerId(), $respondentAnswerIds);
        }

        if($answerIsSelected && empty($value)){
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}