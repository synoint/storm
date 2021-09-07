<?php

namespace Syno\Storm\RequestHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Syno\Storm\Document;
use Syno\Storm\Document\ResponseAnswerValue;
use Syno\Storm\Services;


class Response
{
    CONST ATTR = 'response';

    private Services\Response $responseService;

    public function __construct(Services\Response $responseService)
    {
        $this->responseService = $responseService;
    }

    public function getResponseId(Request $request, int $surveyId):? string
    {
        $result = $request->query->get('id');
        if (!$result) {
            $result = $this->getResponseIdFromSession($request, $surveyId);
        }

        if (null !== $result) {
            if (!is_string($result)) {
                $result = null;
            } else {
                $result = trim($result);
                if (preg_match('/[^a-z0-9\-]/', $result)) {
                    $result = null;
                }
            }
        }

        return $result;
    }

    public function getResponseIdFromSession(Request $request, int $surveyId):? string
    {
        return $request->getSession()->get('id' . $surveyId);
    }

    public function saveResponseIdInSession(Request $request, Document\Response $response)
    {
        $request->getSession()->set('id' . $response->getSurveyId(), $response->getResponseId());
    }

    public function clearResponseIdInSession(Request $request, int $surveyId)
    {
        $request->getSession()->remove('id' . $surveyId);
    }

    public function getSavedResponse(int $surveyId, string $responseId):? Document\Response
    {
        return $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);
    }

    public function setResponse(Request $request, Document\Response $response)
    {
        $request->attributes->set(self::ATTR, $response);
    }

    public function getResponse(Request $request): Document\Response
    {
        $response = $request->attributes->get(self::ATTR);
        if (!$response instanceof Document\Response) {
            throw new \UnexpectedValueException('Response attribute is invalid');
        }

        return $response;
    }

    public function clearResponse(Request $request)
    {
        $request->attributes->remove(self::ATTR);
    }

    public function getNewResponse(Request $request, Document\Survey $survey): Document\Response
    {
        $responseId = $this->getResponseId($request, $survey->getSurveyId());
        $result = $this->responseService->getNew($responseId);
        $result
            ->setSurveyId($survey->getSurveyId())
            ->setSurveyVersion($survey->getVersion())
            ->setMode($this->responseService->getModeByRoute($request->attributes->get('_route')))
            ->setLocale($request->attributes->get('_locale'));

        return $result;
    }

    public function extractParameters(Collection $surveyValues, Request $request): Collection
    {
        $result = new ArrayCollection();
        /** @var Document\Parameter $surveyValue */
        foreach ($surveyValues as $surveyValue) {
            if ($request->query->has($surveyValue->getUrlParam())) {
                $value = clone $surveyValue;
                $value->setValue($request->query->get($value->getUrlParam()));
                $result[] = $value;
            }
        }

        return $result;
    }

    public function saveResponse(Document\Response $response)
    {
        $this->responseService->save($response);
    }

    public function addUserAgent(Request $request, Document\Response $response): Document\Response
    {
        $response->addUserAgent(
            $request->getClientIp(),
            $request->headers->get('User-Agent')
        );

        return $response;
    }

    public function extractAnswers(Collection $questions, ?array $requestData): array
    {
        $questionAnswers = [];

        /** @var Document\Question $question */
        foreach ($questions as $question) {

            $questionAnswers[$question->getQuestionId()] = [];
            if($requestData) {
                foreach ($this->extractQuestionAnswers($question, $requestData) as $answer) {
                    /**@var ResponseAnswerValue $answer */
                    $questionAnswers[$question->getQuestionId()][$answer->getAnswerId()] = $answer->getValue();
                }
            }
        }

        return $questionAnswers;
    }

    public function extractQuestionAnswers(Document\Question $question, array $formData): Collection
    {
        $result = new ArrayCollection();
        switch ($question->getQuestionTypeId()) {
            case Document\Question::TYPE_SINGLE_CHOICE:
                $key = $question->getInputName();
                if (!empty($formData[$key]) &&
                    is_int($formData[$key]) &&
                    $question->answerIdExists($formData[$key])
                ) {
                    if($question->getAnswer($formData[$key])->getIsFreeText()) {
                        $result[] = new Document\ResponseAnswerValue($formData[$key], $this->extractFreeTextValue($formData, $formData[$key], $question));
                    } else {
                        $result[] = new Document\ResponseAnswerValue($formData[$key]);
                    }
                }
                break;
            case Document\Question::TYPE_MULTIPLE_CHOICE:
                $key = $question->getInputName();
                if (!empty($formData[$key]) && is_array($formData[$key])) {
                    foreach ($formData[$key] as $answerId) {
                        if ($question->answerIdExists($answerId)) {
                            if($question->getAnswer($answerId)->getIsFreeText()) {
                                $result[] = new Document\ResponseAnswerValue($answerId, $this->extractFreeTextValue($formData, $answerId, $question));
                            } else {
                                $result[] = new Document\ResponseAnswerValue($answerId);
                            }
                        }
                    }
                }
                break;
            case Document\Question::TYPE_SINGLE_CHOICE_MATRIX:
                foreach (array_keys($question->getRows()) as $rowCode) {
                    $key = $question->getInputName($rowCode);
                    if (!empty($formData[$key]) &&
                        is_int($formData[$key]) &&
                        $question->answerIdExists($formData[$key])
                    ) {
                        $result[] = new Document\ResponseAnswerValue($formData[$key]);
                    }
                }
                break;
            case Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                foreach (array_keys($question->getRows()) as $rowCode) {
                    $key = $question->getInputName($rowCode);
                    if (!empty($formData[$key]) &&
                        is_array($formData[$key])) {
                        foreach ($formData[$key] as $answerId) {
                            if ($question->answerIdExists($answerId)) {
                                $result[] = new Document\ResponseAnswerValue($answerId);
                            }
                        }
                    }
                }
                break;
            case Document\Question::TYPE_TEXT:
                /** @var Document\Answer $answer */
                foreach ($question->getAnswers() as $answer) {
                    $key = $question->getInputName($answer->getAnswerId());
                    if (!empty($formData[$key]) && is_string($formData[$key])) {
                        $value = trim($formData[$key]);
                        $value = filter_var($value, FILTER_SANITIZE_STRING);
                        $value = mb_substr($value, 0, 10000, 'UTF-8');
                        $result[] = new Document\ResponseAnswerValue($answer->getAnswerId(), $value);
                    }
                }
                break;
            case Document\Question::TYPE_LINEAR_SCALE:
                $key = $question->getInputName();
                if (!empty($formData[$key]) && $formData[$key] instanceof Document\Answer) {
                    $result[] = new Document\ResponseAnswerValue($formData[$key]->getAnswerId());
                }
                break;
            case Document\Question::TYPE_LINEAR_SCALE_MATRIX:
                foreach (array_keys($question->getRows()) as $rowCode) {
                    $key = $question->getInputName($rowCode);
                    if (!empty($formData[$key]) && $formData[$key] instanceof Document\Answer) {
                        $result[] = new Document\ResponseAnswerValue($formData[$key]->getAnswerId());
                    }
                }
                break;
        }

        return $result;
    }

    private function extractFreeTextValue(array $formData, int $answerId, Document\Question $question): ?string
    {
        $valueKey = $question->getInputName($answerId);
        if (!empty($formData[$valueKey]) && is_string($formData[$valueKey])) {
            $value = trim($formData[$valueKey]);
            $value = filter_var($value, FILTER_SANITIZE_STRING);
            return mb_substr($value, 0, 10000, 'UTF-8');
        }
        return null;
    }

    public function hasModeChanged(Request $request, string $surveyMode): bool
    {
        return $this->responseService->getModeByRoute($request->attributes->get('_route')) !== $surveyMode;
    }
}
