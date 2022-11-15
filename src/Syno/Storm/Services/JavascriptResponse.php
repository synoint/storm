<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class JavascriptResponse
{
    private ResponseSessionManager $responseSessionManager;
    private Survey                 $surveyService;

    public function __construct(ResponseSessionManager $responseSessionManager, Survey $surveyService)
    {
        $this->responseSessionManager = $responseSessionManager;
        $this->surveyService          = $surveyService;
    }

    public function response()
    {
        $result = [];

        $response = $this->responseSessionManager->getResponse();
//        dump($response->getSurveyId());
//        dump($response->getSurveyVersion());

        $responseSurvey = $this->surveyService->findBySurveyIdAndVersion($response->getSurveyId(),
            $response->getSurveyVersion());
//        dd($responseSurvey->getPages());
        /** @var Document\ResponseAnswer $responseAnswer */
        foreach ($response->getAnswers() as $responseAnswer) {
//            dump($responseAnswer->getQuestionId());
//            dump($this->getQuestionCode($responseSurvey->getPages()->toArray(), $responseAnswer->getQuestionId()));


//            $questionCode = $this->getQuestionCode($responseSurvey->getPages()->toArray(), $responseAnswer->getQuestionId());

            /** @var Document\ResponseAnswerValue $responseAnswerValue */
            foreach ($responseAnswer->getAnswers() as $responseAnswerValue) {
//                dd($responseAnswerValue);
                $answerKeyCode = $this->getAnswerKey($responseSurvey->getPages()->toArray(), $responseAnswerValue->getAnswerId());
                dd($answerKeyCode);
            }

//            dd($responseAnswer);
        }
    }

    private function getAnswerKey(array $pages, int $answerId): string
    {
        /** @var Document\Page $page */
        foreach ($pages as $page) {
            /** @var Document\Question $question */
            foreach ($page->getQuestions() as $question) {
                /** @var Document\Answer $answer */
                foreach ($question->getAnswers() as $answer) {
                    if ($answer->getAnswerId() === $answerId) {
                        return sprintf('%s_%s_%d', $page->getCode(), $question->getCode(), $answer->getCode());
                    }
                }
            }
        }

        return '';
    }
}
