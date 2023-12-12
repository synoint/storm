<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Syno\Storm\Document;
use Syno\Storm\RequestHandler;

class ResponseSessionManager
{
    private Condition               $conditionService;
    private Page                    $pageService;
    private RequestHandler\Answer   $answerHandler;
    private RequestHandler\Page     $pageHandler;
    private RequestHandler\Response $responseHandler;
    private RequestHandler\Survey   $surveyHandler;
    private ResponseSession         $responseSession;

    private ?Collection             $questions = null;

    public function __construct(
        Condition                 $conditionService,
        Page                      $pageService,
        RequestHandler\Answer     $answerHandler,
        RequestHandler\Page       $pageHandler,
        RequestHandler\Response   $responseHandler,
        RequestHandler\Survey     $surveyHandler,
        ResponseSession           $responseSession
    )
    {
        $this->conditionService          = $conditionService;
        $this->pageService               = $pageService;
        $this->answerHandler             = $answerHandler;
        $this->pageHandler               = $pageHandler;
        $this->responseHandler           = $responseHandler;
        $this->surveyHandler             = $surveyHandler;
        $this->responseSession           = $responseSession;
    }

    public function getPage(): Document\Page
    {
        return $this->pageHandler->getPage();
    }

    public function getSurvey(): Document\Survey
    {
        return $this->surveyHandler->getSurvey();
    }

    public function getResponse(): Document\Response
    {
        return $this->responseHandler->getResponse();
    }

    public function getQuestions(): Collection
    {
        if (null === $this->questions) {
            $this->questions =
                $this->conditionService->filterQuestionsByShowCondition(
                    $this->pageHandler->getPage()->getVisibleQuestions(),
                    $this->responseHandler->getResponse()
                );

            $this->questions =
                $this->conditionService->filterQuestionAnswersByShowCondition(
                    $this->questions,
                    $this->responseHandler->getResponse()
                );
        }

        return $this->questions;
    }

    public function getAnswerMap(?array $formData): array
    {
        if ($formData && is_array($formData)) {
            return $this->answerHandler->getAnswerMap($this->getQuestions(), $formData);
        }

        return $this->responseHandler->getResponse()->getAnswerIdValueMap();
    }

    public function saveAnswersFromParams(array $formData)
    {
        $survey = $this->getSurvey();
        $questions = $this->pageService->getSurveyQuestions($survey->getSurveyId(), $survey->getVersion());
        $answers = $this->answerHandler->getAnswers($questions, $formData);

        if (!$answers->isEmpty()) {
            $this->responseSession->saveAnswers($this->responseHandler->getResponse(), $answers);
        }
    }

    public function saveAnswers(array $formData)
    {
        $answers = $this->answerHandler->getAnswers($this->getQuestions(), $formData);

        if (!$answers->isEmpty()) {
            $this->responseSession->saveAnswers($this->responseHandler->getResponse(), $answers);
        }
    }

    public function redirectOnScreenOut(): ?RedirectResponse
    {
        foreach ($this->getQuestions() as $question) {
            if ($question->getScreenoutConditions()->isEmpty()) {
                continue;
            }

            $screenOut = $this->conditionService->applyScreenoutRule(
                $this->responseHandler->getResponse(),
                $question->getScreenoutConditions()
            );
            if (!$screenOut) {
                continue;
            }

            $this->responseHandler->getResponse()->setScreenoutId($screenOut->getScreenoutId());
            if ($screenOut->getType() === Document\ScreenoutCondition::TYPE_QUALITY_SCREENOUT) {
                return $this->responseSession->qualityScreenOut($this->surveyHandler->getSurvey());
            }

            return $this->responseSession->screenOut($this->surveyHandler->getSurvey());
        }

        return null;
    }

    public function redirectOnJump(): ?RedirectResponse
    {
        foreach ($this->getQuestions() as $question) {
            if ($question->getJumpToConditions()->isEmpty()) {
                continue;
            }

            $jump = $this->conditionService->applyJumpRule(
                $this->responseHandler->getResponse(),
                $question->getJumpToConditions()
            );
            if (!$jump) {
                continue;
            }

            return $this->responseSession->jump($this->surveyHandler->getSurvey(), $jump);
        }

        return null;
    }

    public function advance(): RedirectResponse
    {
        $nextPageId = $this->pageHandler->getNextPageId();
        if (!$nextPageId) {
            return $this->responseSession->complete($this->surveyHandler->getSurvey());
        }

        return $this->responseSession->redirectToPage($this->surveyHandler->getId(), $nextPageId);
    }

    public function answeredWithErrors()
    {
        $this->responseSession->answeredWithErrors();
    }

    public function saveProgress()
    {
        $this->responseSession->saveProgress($this->pageHandler->getPage());
    }

    public function isLastPage(int $pageId): bool
    {
        return $pageId == $this->pageHandler->getLastPageId();
    }

    public function redirectToFirstPage(): RedirectResponse
    {
        $firstPageId = $this->pageHandler->getFirstPageId();
        if (!$firstPageId) {
            return $this->responseSession->redirectToPageUnavailable();
        }

        return $this->responseSession->redirectToPage($this->surveyHandler->getId(), $firstPageId);
    }

    public function enableBackButton(int $pageId): bool
    {
        if ($pageId === $this->pageHandler->getFirstPageId()) {
            return false;
        }

        $response = $this->responseHandler->getResponse();
        if ($response->isDebug() || $response->isTest()) {
            return true;
        }

        $survey = $this->surveyHandler->getSurvey();

        return $survey->getConfig()->isBackButtonEnabled();
    }


}
