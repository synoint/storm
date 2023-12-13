<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Syno\Storm\Document;
use Syno\Storm\Traits\HtmlAware;

class ResponseDataLayer
{
    use HtmlAware;

    private Page $pageService;

    public function __construct(Page $pageService)
    {
        $this->pageService = $pageService;
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

        $surveyAnswerMap = $this->getSurveyAnswerMap(
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

        return $result;
    }

    private function getSurveyAnswerMap(int $surveyId, int $version, string $locale): array
    {
        $result = [];
        $surveyAnswers = $this->pageService->getAnswersForDataLayer($surveyId, $version);
        if ($surveyAnswers) {
             $result = $this->convertToMapByAnswerId($surveyAnswers, $locale);
        }

        return $result;
    }

    private function convertToMapByAnswerId($answerData, string $locale): array
    {
        $map = [];
        foreach ($answerData as $page) {
            if (!isset($page['questions'])) {
                continue;
            }
            foreach ($page['questions'] as $question) {
                if (!isset($question['answers'])) {
                    continue;
                }
                foreach ($question['answers'] as $answer) {

                    $mapItem = [
                        'pageCode'     => $page['code'],
                        'questionCode' => $question['code'],
                        'questionText' => !empty($question['text']) ? $this->cleanMarkup($question['text']) : '',
                        'questionType' => $question['questionTypeId']
                    ];

                    $translations = $answer['translations'] ?? [];

                    if (isset($answer['rowCode']) || isset($answer['columnCode'])) {
                        $mapItem['rowCode']    = $answer['rowCode'] ?? null;
                        $mapItem['rowLabel']   = $this->translate($locale, $translations, 'rowLabel');
                        $mapItem['columnCode'] = $answer['columnCode'] ?? null;
                        $mapItem['columnLabel'] = $this->translate($locale, $translations, 'columnLabel');
                    } else {
                        $mapItem['code']  = $answer['code'] ?? null;
                        $mapItem['label'] = $this->translate($locale, $translations, 'label');
                    }

                    $answerId = (int) $answer['answerId'];
                    $map[$answerId] = $mapItem;
                }
            }
        }

        return $map;
    }

    private function translate(string $locale, array $translations, string $key):? string
    {
        foreach ($translations as $translation) {
            if ($translation['locale'] === $locale && isset($translation[$key])) {
                return $translation[$key];
            }
        }

        return null;
    }
}
