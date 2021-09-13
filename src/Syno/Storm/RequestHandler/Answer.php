<?php

namespace Syno\Storm\RequestHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Syno\Storm\Document\Answer as AnswerDocument;
use Syno\Storm\Document\Question;
use Syno\Storm\Document\ResponseAnswer;
use Syno\Storm\Document\ResponseAnswerValue;


class Answer
{
    public function getAnswerMap(Collection $questions, array $formData): array
    {
        $result = [];

        /** @var Question $question */
        foreach ($questions as $question) {
            $result[$question->getQuestionId()] = [];
            foreach ($this->extractAnswers($question, $formData) as $answer) {
                $result[$question->getQuestionId()][$answer->getAnswerId()] = $answer->getValue();
            }
        }

        return $result;
    }

    public function getAnswers(Collection $questions, array $formData): array
    {
        $result = [];
        foreach ($questions as $question) {
            $answers = $this->extractAnswers($question, $formData);
            $result[] = new ResponseAnswer($question->getQuestionId(), $answers);
        }

        return $result;
    }

    /**
     * @param Question $question
     * @param array             $formData
     *
     * @return Collection|ResponseAnswerValue[]
     */
    public function extractAnswers(Question $question, array $formData): Collection
    {
        $result = new ArrayCollection();
        switch ($question->getQuestionTypeId()) {
            case Question::TYPE_SINGLE_CHOICE:
                $key = $question->getInputName();
                if (!empty($formData[$key]) &&
                    is_int($formData[$key]) &&
                    $question->answerIdExists($formData[$key])
                ) {
                    if ($question->getAnswer($formData[$key])->getIsFreeText()) {
                        $result[] = new ResponseAnswerValue(
                            $formData[$key],
                            $this->extractFreeTextValue($formData, $formData[$key], $question)
                        );
                    } else {
                        $result[] = new ResponseAnswerValue($formData[$key]);
                    }
                }
                break;
            case Question::TYPE_MULTIPLE_CHOICE:
                $key = $question->getInputName();
                if (!empty($formData[$key]) && is_array($formData[$key])) {
                    foreach ($formData[$key] as $answerId) {
                        if ($question->answerIdExists($answerId)) {
                            if ($question->getAnswer($answerId)->getIsFreeText()) {
                                $result[] = new ResponseAnswerValue(
                                    $answerId,
                                    $this->extractFreeTextValue($formData, $answerId, $question)
                                );
                            } else {
                                $result[] = new ResponseAnswerValue($answerId);
                            }
                        }
                    }
                }
                break;
            case Question::TYPE_SINGLE_CHOICE_MATRIX:
                foreach (array_keys($question->getRows()) as $rowCode) {
                    $key = $question->getInputName($rowCode);
                    if (!empty($formData[$key]) &&
                        is_int($formData[$key]) &&
                        $question->answerIdExists($formData[$key])
                    ) {
                        $result[] = new ResponseAnswerValue($formData[$key]);
                    }
                }
                break;
            case Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                foreach (array_keys($question->getRows()) as $rowCode) {
                    $key = $question->getInputName($rowCode);
                    if (!empty($formData[$key]) &&
                        is_array($formData[$key])) {
                        foreach ($formData[$key] as $answerId) {
                            if ($question->answerIdExists($answerId)) {
                                $result[] = new ResponseAnswerValue($answerId);
                            }
                        }
                    }
                }
                break;
            case Question::TYPE_TEXT:
                /** @var AnswerDocument $answer */
                foreach ($question->getAnswers() as $answer) {
                    $key = $question->getInputName($answer->getAnswerId());
                    if (!empty($formData[$key]) && is_string($formData[$key])) {
                        $value    = trim($formData[$key]);
                        $value    = filter_var($value, FILTER_SANITIZE_STRING);
                        $value    = mb_substr($value, 0, 10000, 'UTF-8');
                        $result[] = new ResponseAnswerValue($answer->getAnswerId(), $value);
                    }
                }
                break;
            case Question::TYPE_LINEAR_SCALE:
                $key = $question->getInputName();
                if (!empty($formData[$key]) && $formData[$key] instanceof AnswerDocument) {
                    $result[] = new ResponseAnswerValue($formData[$key]->getAnswerId());
                }
                break;
            case Question::TYPE_LINEAR_SCALE_MATRIX:
                foreach (array_keys($question->getRows()) as $rowCode) {
                    $key = $question->getInputName($rowCode);
                    if (!empty($formData[$key]) && $formData[$key] instanceof AnswerDocument) {
                        $result[] = new ResponseAnswerValue($formData[$key]->getAnswerId());
                    }
                }
                break;
        }

        return $result;
    }

    private function extractFreeTextValue(array $formData, int $answerId, Question $question): ?string
    {
        $valueKey = $question->getInputName($answerId);
        if (!empty($formData[$valueKey]) && is_string($formData[$valueKey])) {
            $value = trim($formData[$valueKey]);
            $value = filter_var($value, FILTER_SANITIZE_STRING);

            return mb_substr($value, 0, 10000, 'UTF-8');
        }

        return null;
    }


}
