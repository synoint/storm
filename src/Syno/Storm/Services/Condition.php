<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\Collection;
use JWadhams;
use Syno\Storm\Document;

class Condition
{
    public function applyScreenoutRule(Document\Response $response, Collection $screenoutConditions): ?Document\ScreenoutCondition
    {
        foreach($screenoutConditions as $screenoutCondition){
            if(JWadhams\JsonLogic::apply(json_decode($screenoutCondition->getRule()), $response->getAnswerIdMap())){
                return $screenoutCondition;
            }
        }

        return null;
    }

    public function applyJumpRule(Document\Response $response, Collection $jumpToConditions): ?Document\JumpToCondition
    {
        foreach($jumpToConditions as $jumpToCondition){
            if(JWadhams\JsonLogic::apply(json_decode($jumpToCondition->getRule()), $response->getAnswerIdMap())){
                return $jumpToCondition;
            }
        }

        return null;
    }

    public function applyShowRule(Document\Response $response, Collection $showConditions): bool
    {
        foreach($showConditions as $showCondition){
            if(JWadhams\JsonLogic::apply(json_decode($showCondition->getRule()), $response->getAnswerIdMap())){
                return true;
            }
        }

        return false;
    }

    /**
     * @param Collection        $questions
     * @param Document\Response $response
     *
     * @return Collection|Document\Question[]
     */
    public function filterQuestionsByShowCondition(Collection $questions, Document\Response $response): Collection
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
