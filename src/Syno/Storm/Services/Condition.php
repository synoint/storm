<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\Collection;
use JWadhams;
use Syno\Storm\Document;

class Condition
{
    public function applyScreenoutRule(Document\Response $response, $screenoutConditions)
    {
        foreach($screenoutConditions as $screenoutCondition){
            if(JWadhams\JsonLogic::apply(json_decode($screenoutCondition->getRule()), $response->getLastAnswersId())){
                return $screenoutCondition->getUrlType();
            }
        }

        return null;
    }

    public function applyJumpToRule(Document\Response $response, $jumpToConditions)
    {
        foreach($jumpToConditions as $jumpToCondition){
            if(JWadhams\JsonLogic::apply(json_decode($jumpToCondition->getRule()), $response->getLastAnswersId())){
                return $jumpToCondition->getDestination();
            }
        }

        return null;
    }

    public function applyShowRule(Document\Response $response, $showConditions)
    {
        foreach($showConditions as $showCondition){
            if(JWadhams\JsonLogic::apply(json_decode($showCondition->getRule()), $response->getLastAnswersId())){
                return true;
            }
        }

        return false;
    }

    public function filterQuestionsByShowCondition(Collection $questions, Document\Response $response)
    {
        return $questions->filter(
            function ($question) use ($response) {
                /** @var Document\Question $question */
                $showConditions = $question->getShowConditions();

                return $showConditions->count() == 0 || ($showConditions->count() && $this->applyShowRule($response, $showConditions));
            }
        );
    }
}