<?php

namespace Syno\Storm\Services;

use JWadhams;

class Condition
{
    public function applyScreenoutRule(array $responsesArray, $screenoutConditions)
    {
        $redirectUrl = null;

        foreach($screenoutConditions as $screenoutCondition){
            $conditionData = [];

            foreach($screenoutCondition->getRuleQuestionIds() as $questionId){
                if (isset($responsesArray[$questionId])) {
                    foreach($responsesArray[$questionId] as $answer){
                        $conditionData[$answer] =  1;
                    }
                }
            }

            if(JWadhams\JsonLogic::apply(json_decode($screenoutCondition->getRule()), $conditionData)){
                return $screenoutCondition->getUrl();
            }
        }

        return $redirectUrl;
    }

    public function applyJumpToRule(array $responsesArray, $jumpToConditions)
    {
        $redirectUrl = null;

        foreach($jumpToConditions as $jumpToCondition){
            $conditionData = [];

            foreach($jumpToCondition->getRuleQuestionIds() as $questionId){
                if (isset($responsesArray[$questionId])) {
                    foreach($responsesArray[$questionId] as $answer){
                        $conditionData[$answer] =  1;
                    }
                }
            }

            if(JWadhams\JsonLogic::apply(json_decode($jumpToCondition->getRule()), $conditionData)){
                return $jumpToCondition->getDestination();
            }
        }

        return $redirectUrl;
    }

    public function applyShowRule(array $responsesArray, $showConditions)
    {
        foreach($showConditions as $showCondition){
            $conditionData = [];

            foreach($showCondition->getRuleQuestionIds() as $questionId){
                if (isset($responsesArray[$questionId])) {
                    foreach($responsesArray[$questionId] as $answer){
                        $conditionData[$answer] =  1;
                    }
                }
            }

            if(JWadhams\JsonLogic::apply(json_decode($showCondition->getRule()), $conditionData)){
                return true;
            }
        }

        return false;
    }

    public function filterQuestionsByShowCondition( $questions, array $responsesArray)
    {
        $filteredQuestions = clone $questions;

        foreach($questions as $key => $question){
            $showConditions = $question->getShowConditions();

            if(($showConditions->count() && !$this->applyShowRule($responsesArray, $showConditions))){
                $filteredQuestions->remove($key);
            }
        }

        return $filteredQuestions;
    }
}