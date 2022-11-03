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

    /**
     * @return Collection|ResponseAnswer[]
     */
    public function getAnswers(Collection $questions, array $formData): Collection
    {
        $result = new ArrayCollection();
        foreach ($questions as $question) {
            $answers = $this->extractAnswers($question, $formData);

            if (!$answers->isEmpty()) {
                $result[] = new ResponseAnswer($question->getQuestionId(), $answers);
            }
        }

        return $result;
    }

    /**
     * @return Collection|ResponseAnswerValue[]
     */
    public function extractAnswers(Question $question, array $formData): Collection
    {
        $result = new ArrayCollection();
        switch ($question->getQuestionTypeId()) {
            case Question::TYPE_SINGLE_CHOICE:
                $key = $question->getCode();

                if ("" !== $formData[$key] && $question->answerCodeExists($formData[$key])) {

                    $answer = $question->getAnswerByCode($formData[$key]);

                    if ($answer->getIsFreeText()) {
                        $result[] = new ResponseAnswerValue(
                            $answer->getAnswerId(),
                            $this->extractFreeTextValue($formData, $answer->getCode(), $question)
                        );
                    } else {
                        $result[] = new ResponseAnswerValue($answer->getAnswerId());
                    }
                }
                break;
            case Question::TYPE_MULTIPLE_CHOICE:
                $key = $question->getCode();
                if ("" !== $formData[$key] && is_array($formData[$key])) {
                    foreach ($formData[$key] as $answerCode) {

                        if ($question->answerCodeExists($answerCode)) {

                            $answer = $question->getAnswerByCode($answerCode);

                            if ($answer->getIsFreeText()) {
                                $result[] = new ResponseAnswerValue(
                                    $answer->getAnswerId(),
                                    $this->extractFreeTextValue($formData,  $answer->getCode(), $question)
                                );
                            } else {
                                $result[] = new ResponseAnswerValue($answer->getAnswerId());
                            }
                        }
                    }
                }
                break;
            case Question::TYPE_SINGLE_CHOICE_MATRIX:
                foreach (array_keys($question->getRows()) as $rowCode) {
                    $key = $question->getInputName($rowCode);

                    if ("" !== $formData[$key]) {

                        $answer = $question->getAnswerByRowAndColumn($this->extractAnswerCode($key), $formData[$key]);

                        if($answer) {
                            $result[] = new ResponseAnswerValue($answer->getAnswerId());
                        }
                    }
                }
                break;
            case Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                foreach (array_keys($question->getRows()) as $rowCode) {
                    $key = $question->getInputName($rowCode);
                    if ("" !== $formData[$key] &&
                        is_array($formData[$key])) {
                        foreach ($formData[$key] as $column) {

                            $answer = $question->getAnswerByRowAndColumn($this->extractAnswerCode($key), $column);

                            if ($answer) {
                                $result[] = new ResponseAnswerValue($answer->getAnswerId());
                            }
                        }
                    }
                }
                break;
            case Question::TYPE_TEXT:
                /** @var AnswerDocument $answer */
                foreach ($question->getAnswers() as $answer) {
                    $key = $question->getInputName($answer->getCode());
                    if ("" !== $formData[$key] && is_string($formData[$key])) {
                        $value    = trim($formData[$key]);
                        $value    = filter_var($value, FILTER_SANITIZE_STRING);
                        $value    = mb_substr($value, 0, 10000, 'UTF-8');
                        $result[] = new ResponseAnswerValue($answer->getAnswerId(), $value);
                    }
                }
                break;
            case Question::TYPE_LINEAR_SCALE:
                $key = $question->getInputName();
                if ("" !== $formData[$key]) {

                    $code = $formData[$key];

                    if($formData[$key] instanceof AnswerDocument){
                        $code = $formData[$key]->getCode();

                    }

                    $answer = $question->getAnswerByCode($code);

                    $result[] = new ResponseAnswerValue($answer->getAnswerId());
                }
                break;
            case Question::TYPE_LINEAR_SCALE_MATRIX:

                foreach (array_keys($question->getRows()) as $rowCode) {
                    $key = $question->getInputName($rowCode);

                    if ("" !== $formData[$key]) {

                        $column = $formData[$key];

                        if($formData[$key] instanceof AnswerDocument){
                            $column = $formData[$key]->getColumnCode();
                        }

                        $answer = $question->getAnswerByRowAndColumn($this->extractAnswerCode($key), $column);

                        if(!empty($answer)){

                            $result[] = new ResponseAnswerValue($answer->getAnswerId());
                        }
                    }
                }

                break;
        }

        return $result;
    }

    private function extractFreeTextValue(array $formData, int $answerCode, Question $question): ?string
    {
        $valueKey = $question->getInputName($answerCode);
        if ("" !== $formData[$valueKey] && is_string($formData[$valueKey])) {
            $value = trim($formData[$valueKey]);
            $value = filter_var($value, FILTER_SANITIZE_STRING);

            return mb_substr($value, 0, 10000, 'UTF-8');
        }

        return null;
    }

    private function extractAnswerCode(string $key): ?string
    {
        $split = explode("_", $key);

        return count($split) > 1 ? $split[count($split)-1] : null;
    }

}
