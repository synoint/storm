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

    public function responseResults(): array
    {
        $response = $this->responseSessionManager->getResponse();
        $responseSurvey = $this->surveyService->findBySurveyIdAndVersion($response->getSurveyId(), $response->getSurveyVersion());

        $result['version'] = $response->getSurveyVersion();
        $result['locale'] = $response->getLocale();
        $result['mode'] = $response->getMode();
        $result['createdAt'] = $response->getCreatedAt()->getTimestamp();
        $result['answers'] = [];

        /** @var Document\ResponseAnswer $responseAnswer */
        foreach ($response->getAnswers() as $responseAnswer) {
            /** @var Document\ResponseAnswerValue $responseAnswerValue */
            foreach ($responseAnswer->getAnswers() as $index => $responseAnswerValue) {
                $answerKeyCode = $this->getAnswerKey($responseSurvey->getPages()->toArray(), $responseAnswerValue->getAnswerId());

                if (!empty($answerKeyCode)) {
                    $result['answers'][$answerKeyCode] = $responseAnswerValue->getValue();
                }
            }

        }

        return $result;
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
                        if ($answer->getRowCode() || $answer->getColumnCode()) {
                            return sprintf('%s_%s_%d_%d', $page->getCode(), $question->getCode(), $answer->getRowCode(), $answer->getColumnCode());
                        }

                        return sprintf('%s_%s_%d', $page->getCode(), $question->getCode(), $answer->getCode());
                    }
                }
            }
        }

        return '';
    }
}
