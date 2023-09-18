<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JWadhams;
use Syno\Storm\Document;

class Condition
{
    public function applySurveyConditionRule(Document\Response $response, string $rule): bool
    {
        if (empty(json_decode($rule))) {
            return false;
        }

        return JWadhams\JsonLogic::apply(json_decode($rule), $response->getAnswerIdMap()) ?? false;
    }

    public function applyScreenoutRule(
        Document\Response $response,
        Collection $screenoutConditions
    ): ?Document\ScreenoutCondition {
        /** @var Document\ScreenoutCondition $screenoutCondition */
        foreach ($screenoutConditions as $screenoutCondition) {
            if (JWadhams\JsonLogic::apply(json_decode($screenoutCondition->getRule()), $response->getAnswerIdMap())) {
                return $screenoutCondition;
            }
        }

        return null;
    }

    public function applyJumpRule(Document\Response $response, Collection $jumpToConditions): ?Document\JumpToCondition
    {
        /** @var Document\JumpToCondition $jumpToCondition */
        foreach ($jumpToConditions as $jumpToCondition) {
            if (JWadhams\JsonLogic::apply(json_decode($jumpToCondition->getRule()), $response->getAnswerIdMap())) {
                return $jumpToCondition;
            }
        }

        return null;
    }

    public function applyShowRule(Document\Response $response, Collection $showConditions): bool
    {
        /** @var Document\ShowCondition $showCondition */
        foreach ($showConditions as $showCondition) {
            if (JWadhams\JsonLogic::apply(json_decode($showCondition->getRule()), $response->getAnswerIdMap())) {
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

                return $showConditions->count() == 0 || ($showConditions->count() && $this->applyShowRule($response,
                            $showConditions));
            }
        );
    }

    /**
     * @param Collection        $questions
     * @param Document\Response $response
     *
     * @return ArrayCollection|Document\Question[]
     */
    public function filterQuestionAnswersByShowCondition(
        Collection $questions,
        Document\Response $response
    ): ArrayCollection {
        $filteredResults = new ArrayCollection();

        /** @var Document\Question $question */
        foreach ($questions as $question) {
            $answers = $question->getAnswers()->filter(
                function ($answer) use ($response) {
                    /** @var Document\Answer $answer */
                    $showConditions = $answer->getShowConditions();

                    return $showConditions->count() == 0 || ($showConditions->count() && $this->applyShowRule($response,
                                $showConditions));
                }
            );

            $filteredQuestion = clone $question;
            $filteredQuestion = $filteredQuestion->setAnswers($answers);
            $filteredResults->add($filteredQuestion);
        }

        return $filteredResults;
    }
}
