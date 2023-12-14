<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class ResponseDataLayer
{
    private SurveyAnswerMap $surveyAnswerMap;

    public function __construct(SurveyAnswerMap $surveyAnswerMap)
    {
        $this->surveyAnswerMap = $surveyAnswerMap;
    }

    public function getData(Document\Survey $survey, Document\Response $response): array
    {
        return [
            'id'         => $response->getResponseId(),
            'parameters' => $this->getParameters($response),
            'answers'    => $this->getAnswers($survey, $response),
        ];
    }

    private function getParameters(Document\Response $response): array
    {
        $result = [];
        /** @var Document\Parameter $parameter */
        foreach ($response->getParameters() as $parameter) {
            $result[$parameter->getUrlParam()] = $parameter->getValue();
        }

        return $result;
    }

    private function getAnswers(Document\Survey $survey, Document\Response $response): array
    {
        $result = [];

        if (!$response->getAnswers()->isEmpty()) {
            $surveyAnswerMap = $this->surveyAnswerMap->get(
                $survey->getSurveyId(),
                $survey->getVersion(),
                $response->getLocale()
            );

            /** @var Document\ResponseAnswer $responseAnswer */
            foreach ($response->getAnswers() as $responseAnswer) {
                /** @var Document\ResponseAnswerValue $responseAnswerValue */
                foreach ($responseAnswer->getAnswers() as $responseAnswerValue) {
                    $answer = $surveyAnswerMap[$responseAnswerValue->getAnswerId()] ?? [];
                    if (null !== $responseAnswerValue->getValue()) {
                        $answer['value'] = $responseAnswerValue->getValue();
                    }
                    $result[] = $answer;
                }
            }
        }

        return $result;
    }


}
