<?php

namespace Syno\Storm\Services;

use Syno\Storm\Document;
use Doctrine\Common\Collections\Collection;

class Question
{
    public function isSelectedAnswersExclusive(Document\Question $question, ?array $selectedAnswers): bool
    {
        if ($selectedAnswers) {
            foreach ($selectedAnswers as $selectedAnswer) {
                if ($question->getAnswer($selectedAnswer)->getIsExclusive()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function buildCodeKeyArray(Collection $questions): array
    {
        $questionCodes = [];

        foreach ($questions as $question) {
            $questionCodes[$question->getCode()] = $question;
        }

        return $questionCodes;
    }
}
