<?php

namespace Syno\Storm\Services;

use Syno\Storm\Document;

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
}
