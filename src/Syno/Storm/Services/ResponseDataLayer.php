<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class ResponseDataLayer
{
    private ResponseSessionManager $responseSessionManager;

    public function __construct(ResponseSessionManager $responseSessionManager)
    {
        $this->responseSessionManager = $responseSessionManager;
    }

    public function getData(): array
    {
        $response       = $this->responseSessionManager->getResponse();
        $responseSurvey = $this->responseSessionManager->getSurvey();

        $result['responseId'] = $response->getResponseId();
        $result['answers']    = [];

        /** @var Document\ResponseAnswer $responseAnswer */
        foreach ($response->getAnswers() as $responseAnswer) {
            /** @var Document\ResponseAnswerValue $responseAnswerValue */
            foreach ($responseAnswer->getAnswers() as $responseAnswerValue) {
                $answerResponse          = $this->getAnswerResponse($responseSurvey, $responseAnswerValue->getAnswerId(),
                    $response->getLocale());
                $answerResponse['value'] = ($responseAnswerValue->getValue()) ?: '';

                $result['answers'][] = $answerResponse;
            }
        }

        return $result;
    }

    private function getAnswerResponse(Document\Survey $survey, int $answerId, string $locale): array
    {
        $result = [];

        /** @var Document\Page $page */
        foreach ($survey->getPages() as $page) {
            /** @var Document\Question $question */
            foreach ($page->getQuestions() as $question) {
                $question->setCurrentLocale($locale);
                /** @var Document\Answer $answer */
                foreach ($question->getAnswers() as $answer) {
                    if ($answer->getAnswerId() === $answerId) {
                        $answer->setCurrentLocale($locale);

                        $result['pageCode']     = $page->getCode();
                        $result['questionCode'] = $question->getCode();
                        $result['questionText'] = $question->getText();
                        $result['questionType'] = $question->getQuestionTypeId();

                        if ($answer->getRowCode() || $answer->getColumnCode()) {
                            $result['rowCode']     = $answer->getRowCode();
                            $result['rowLabel']    = $answer->getRowLabel();
                            $result['columnCode']  = $answer->getColumnCode();
                            $result['columnLabel'] = $answer->getColumnLabel();
                        } else {
                            $result['code']  = $answer->getCode();
                            $result['label'] = $answer->getLabel();
                        }
                    }
                }
            }
        }

        return $result;
    }
}
