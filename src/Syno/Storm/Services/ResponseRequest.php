<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Syno\Storm\Document;
use Symfony\Component\HttpFoundation;

class ResponseRequest
{
    CONST ATTR = 'response';

    /** @var Response */
    private $responseService;

    /**
     * @param Response $responseService
     */
    public function __construct(Response $responseService)
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
            $result = $request->getSession()->get('id:' . $surveyId);
        }

        return $result;
    }

    public function saveResponseIdInSession(Request $request, Document\Response $response)
    {
        $request->getSession()->set('id:' . $response->getSurveyId(), $response->getResponseId());
    }

    /**
     * @param Request $request
     * @param int     $surveyId
     *
     * @return string|null
     */
    public function getResponseIdFromCookie(Request $request, int $surveyId)
    {
        return $request->cookies->get('id:' . $surveyId);
    }

    /**
     * @param Document\Response $response
     *
     * @return Cookie
     */
    public function getResponseIdCookie(Document\Response $response)
    {
        return new Cookie('id:' . $response->getSurveyId(), $response->getResponseId(), time() + 3600);
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
     * @param Request                 $request
     * @param HttpFoundation\Response $response
     * @param int                     $surveyId
     */
    public function clearResponse(Request $request, HttpFoundation\Response $response, int $surveyId)
    {
        $request->attributes->remove(self::ATTR);
        $request->getSession()->remove('id:' . $surveyId);
        $response->headers->clearCookie('id:'. $surveyId);
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
            ->setMode(
                $this->responseService->getMode($request->attributes->get('_route'))
            )
            ->setLocale($request->attributes->get('_locale'));

        return $result;
    }

    /**
     * @param Collection $surveyValues
     * @param Request    $request
     *
     * @return ArrayCollection
     */
    public function extractHiddenValues(Collection $surveyValues, Request $request)
    {
        $result = new ArrayCollection();
        /** @var Document\HiddenValue $surveyValue */
        foreach ($surveyValues as $surveyValue) {
            if ($request->query->has($surveyValue->urlParam)) {
                $value = clone $surveyValue;
                if ($value->type === Document\HiddenValue::TYPE_INT) {
                    $value->value = $request->query->getInt($value->urlParam);
                } else {
                    $value->value = $request->query->get($value->urlParam);
                }
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
     * @param Document\Question $question
     * @param array             $formData
     *
     * @return array|ArrayCollection
     */
    public function extractAnswers(Document\Question $question, array $formData)
    {
        $result = new ArrayCollection();
        switch ($question->getQuestionTypeId()) {
            case Document\Question::TYPE_SINGLE_CHOICE:
                $key = $question->getInputName();
                if (!empty($formData[$key]) &&
                    is_int($formData[$key]) &&
                    $question->answerIdExists($formData[$key])
                ) {
                    $result[] = new Document\ResponseAnswer($formData[$key]);
                }
                break;
            case Document\Question::TYPE_MULTIPLE_CHOICE:
                $key = $question->getInputName();
                if (!empty($formData[$key]) && is_array($formData[$key])) {
                    foreach ($formData[$key] as $answerId) {
                        if ($question->answerIdExists($answerId)) {
                            $result[] = new Document\ResponseAnswer($answerId);
                        }
                    }
                }
                break;
            case Document\Question::TYPE_SINGLE_CHOICE_MATRIX:
                foreach (array_keys($question->getRows()) as $rowCode) {
                    if (!empty($formData[$rowCode]) &&
                        is_int($formData[$rowCode]) &&
                        $question->answerIdExists($formData[$rowCode])
                    ) {
                        $result[] = new Document\ResponseAnswer($formData[$rowCode]);
                    }
                }
                break;
            case Document\Question::TYPE_MULTIPLE_CHOICE_MATRIX:
                foreach (array_keys($question->getRows()) as $rowCode) {
                    if (!empty($formData[$rowCode]) && is_array($formData[$rowCode])) {
                        foreach ($formData[$rowCode] as $answerId) {
                            if ($question->answerIdExists($answerId)) {
                                $result[] = new Document\ResponseAnswer($answerId);
                            }
                        }
                    }
                }
                break;
            case Document\Question::TYPE_TEXT:
                /** @var Document\Answer $answer */
                foreach ($question->getAnswers() as $answer) {
                    $key = $answer->getAnswerId();
                    if (!empty($formData[$key]) && is_string($formData[$key])) {
                        $value = trim($formData[$key]);
                        $value = filter_var($value, FILTER_SANITIZE_STRING);
                        $value = mb_substr($value, 0, 10000, 'UTF-8');
                        $result[] = new Document\ResponseAnswer($key, $value);
                    }
                }
                break;
        }

        return $result;
    }
}
