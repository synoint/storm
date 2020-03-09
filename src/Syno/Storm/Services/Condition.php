<?php

namespace Syno\Storm\Services;

use JWadhams;
use Syno\Storm\Document;

class Condition
{
    public function applyScreenoutRule(Document\Response $responses, $screenoutConditions)
    {
        $redirectUrl = null;

        foreach($screenoutConditions as $screenoutCondition){
            if(JWadhams\JsonLogic::apply(json_decode($screenoutCondition->getRule()), $responses->getLastAnswersId())){
                return $screenoutCondition->getUrl();
            }
        }

        return $redirectUrl;
    }

    public function applyJumpToRule(Document\Response $responses, $jumpToConditions)
    {
        $redirectUrl = null;

        foreach($jumpToConditions as $jumpToCondition){
            if(JWadhams\JsonLogic::apply(json_decode($jumpToCondition->getRule()), $responses->getLastAnswersId())){
                return $jumpToCondition->getDestination();
            }
        }

        return $redirectUrl;
    }

    public function applyShowRule(Document\Response $responses, $showConditions)
    {
        foreach($showConditions as $showCondition){
            if(JWadhams\JsonLogic::apply(json_decode($showCondition->getRule()), $responses->getLastAnswersId())){
                return true;
            }
        }

        return false;
    }

    public function filterQuestionsByShowCondition( $questions, Document\Response $responses)
    {
        $filteredQuestions = clone $questions;

        foreach($questions as $key => $question){
            $showConditions = $question->getShowConditions();

            if(($showConditions->count() && !$this->applyShowRule($responses, $showConditions))){
                $filteredQuestions->remove($key);
            }
        }

        return $filteredQuestions;
    }
}