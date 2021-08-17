<?php

namespace Syno\Storm\RequestHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Syno\Storm\Document;
use Syno\Storm\Document\ResponseAnswerValue;
use Syno\Storm\Services;


class Response
{
    CONST ATTR = 'response';

    /** @var Services\Response */
    private $responseService;

    /**
     * @param Services\Response $responseService
     */
    public function __construct(Services\Response $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * @param Request $request
     * @param int     $surveyId
     *
     * @return string|null
     */
    public function getResponseId(Request $request, int $surveyId)
    {
        $result = $request->query->get('id');
        if (!$result) {
            $result = $this->getResponseIdFromSession($request, $surveyId);
            if (!$result) {
                $result = $this->getResponseIdFromCookie($request, $surveyId);
            }
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

    /**
     * @param Request $request
     * @param int     $surveyId
     *
     * @return string|null
     */
    public function getResponseIdFromSession(Request $request, int $surveyId)
    {
        $result = null;
        if ($request->hasPreviousSession()) {
            $result = $request->getSession()->get('id' . $surveyId);
        }

        return $result;
    }

    public function saveResponseIdInSession(Request $request, Document\Response $response)
    {
        $request->getSession()->set('id' . $response->getSurveyId(), $response->getResponseId());
    }

    public function clearResponseIdInSession(Request $request, int $surveyId)
    {
        $request->getSession()->remove('id' . $surveyId);
    }

    /**
     * @param Request $request
     * @param int     $surveyId
     *
     * @return string|null
     */
    public function getResponseIdFromCookie(Request $request, int $surveyId)
    {
        return $request->cookies->get('id' . $surveyId);
    }

    /**
     * @param Document\Response $response
     *
     * @return Cookie
     */
    public function getResponseIdCookie(Document\Response $response)
    {
        return new Cookie(
            'id' . $response->getSurveyId(),
            $response->getResponseId(),
            time() + 3600,
            '/',
            null,
            null,
            true,
            false,
            'None'
        );
    }

    /**
     * @param HttpResponse $response
     * @param int          $surveyId
     */
    public function clearResponseIdCookie(HttpResponse $response, int $surveyId)
    {
        $response->headers->clearCookie('id'. $surveyId);
    }

    /**
     * @param int    $surveyId
     * @param string $responseId
     *
     * @return Document\Response|null
     */
    public function getSavedResponse(int $surveyId, string $responseId)
    {
        return $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);
    }

    /**
     * @param Request         $request
     * @param Document\Response $response
     */
    public function setResponse(Request $request, Document\Response $response)
    {
        $request->attributes->set(self::ATTR, $response);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function hasResponse(Request $request)
    {
        return $request->attributes->has(self::ATTR);
    }

    /**
     * @param Request $request
     *
     * @return Document\Response
     */
    public function getResponse(Request $request)
    {
        $response = $request->attributes->get(self::ATTR);
        if (!$response instanceof Document\Response) {
            throw new \UnexpectedValueException('Response attribute is invalid');
        }

        return $response;
    }

    /**
     * @param Request $request
     */
    public function clearResponse(Request $request)
    {
        $request->attributes->remove(self::ATTR);
    }

    /**
     * @param Request         $request
     * @param Document\Survey $survey
     *
     * @return Document\Response
     */
    public function getNewResponse(Request $request, Document\Survey $survey)
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

    /**
     * @param Collection $surveyValues
     * @param Request    $request
     *
     * @return ArrayCollection
     */
    public function extractParameters(Collection $surveyValues, Request $request)
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

    /**
     * @param Document\Response $response
     */
    public function saveResponse(Document\Response $response)
    {
        $this->responseService->save($response);
    }

    /**
     * @param Request           $request
     * @param Document\Response $response
     *
     * @return Document\Response
     */
    public function addUserAgent(Request $request, Document\Response $response)
    {
        $response->addUserAgent(
            $request->getClientIp(),
            $request->headers->get('User-Agent')
        );

        return $response;
    }

    public function extractAnswers(Collection $questions, ?array $requestData)
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

    /**
     * @param Document\Question $question
     * @param array             $formData
     *
     * @return array|ArrayCollection
     */
    public function extractQuestionAnswers(Document\Question $question, array $formData)
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

    /**
     * @param array             $formData
     * @param int               $answerId
     * @param Document\Question $question
     *
     * @return string
     */
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

    public function hasModeChanged(Request $request, string $surveyMode)
    {
        return $this->responseService->getModeByRoute($request->attributes->get('_route')) !== $surveyMode;
    }
}
