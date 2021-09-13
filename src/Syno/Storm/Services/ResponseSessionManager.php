<?php

namespace Syno\Storm\Services;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Doctrine\Common\Collections\Collection;
use Syno\Storm\Document;
use Syno\Storm\RequestHandler;

class ResponseSessionManager
{
    private Condition               $conditionService;
    private RequestHandler\Answer   $answerHandler;
    private RequestHandler\Page     $pageHandler;
    private RequestHandler\Response $responseHandler;
    private RequestHandler\Survey   $surveyHandler;
    private ResponseSession         $responseSession;

    private ?Collection $questions = null;

    public function __construct(
        Condition $conditionService,
        RequestHandler\Answer $answerHandler,
        RequestHandler\Page $pageHandler,
        RequestHandler\Response $responseHandler,
        RequestHandler\Survey $surveyHandler,
        ResponseSession $responseSession
    )
    {
        $this->conditionService = $conditionService;
        $this->answerHandler    = $answerHandler;
        $this->pageHandler      = $pageHandler;
        $this->responseHandler  = $responseHandler;
        $this->surveyHandler    = $surveyHandler;
        $this->responseSession  = $responseSession;
    }

    public function getSurvey(): Document\Survey
    {
        return $this->surveyHandler->getSurvey();
    }

    public function getPage(): Document\Page
    {
        return $this->pageHandler->getPage();
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
                    $this->pageHandler->getPage()->getQuestions(),
                    $this->responseHandler->getResponse()
                );
        }

        return $this->questions;
    }

    public function getAnswerMap(?array $formData): array
    {
        if ($formData) {
            return $this->answerHandler->getAnswerMap($this->getQuestions(), $formData);
        }

        return $this->responseHandler->getResponse()->getLatestSavedAnswers();
    }

    public function saveAnswers(array $formData)
    {
        $this->responseSession->saveAnswers(
            $this->responseHandler->getResponse(),
            $this->answerHandler->getAnswers($this->getQuestions(), $formData)
        );
    }

    public function redirectOnScreenOut():? RedirectResponse
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

    public function redirectOnJump():? RedirectResponse
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
        $nextPage = $this->getNextPage($this->pageHandler->getId());
        if (!$nextPage) {
            return $this->responseSession->complete($this->surveyHandler->getSurvey());
        }

        return $this->responseSession->nextPage($this->surveyHandler->getId(), $nextPage->getPageId());
    }

    public function answeredWithErrors()
    {
        $this->responseSession->answeredWithErrors();
    }

    public function saveProgress()
    {
        $this->responseSession->saveProgress($this->pageHandler->getPage());
    }

    private function getNextPage(int $pageId): ?Document\Page
    {
        $nextPage = $this->surveyHandler->getSurvey()->getNextPage($pageId);
        if ($nextPage && !$nextPage->getQuestions()->isEmpty()) {
            if (
                $this->conditionService->filterQuestionsByShowCondition(
                    $nextPage->getQuestions(), $this->responseHandler->getResponse()
                )->isEmpty()
            ) {
                $nextPage = $this->getNextPage($nextPage->getPageId());
            }
        }

        return $nextPage;
    }


}
