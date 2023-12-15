<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Syno\Storm\Document;
use Syno\Storm\RequestHandler;

class PageFinder
{
    private Condition                 $conditionService;
    private Page                      $pageService;
    private RandomizedResponseSession $randomizedResponseSession;
    private RequestHandler\Response   $responseHandler;
    private RequestHandler\Survey     $surveyHandler;

    public function __construct(
        Condition                 $conditionService,
        Page                      $pageService,
        RandomizedResponseSession $randomizedResponseSession,
        RequestHandler\Response   $responseHandler,
        RequestHandler\Survey     $surveyHandler
    )
    {
        $this->conditionService          = $conditionService;
        $this->pageService               = $pageService;
        $this->randomizedResponseSession = $randomizedResponseSession;
        $this->responseHandler           = $responseHandler;
        $this->surveyHandler             = $surveyHandler;
    }


    public function getFirstPageId():? int
    {
        $survey = $this->surveyHandler->getSurvey();
        $firstPageId = $this->randomizedResponseSession->getFirstPageId();
        if (!$firstPageId) {
            $firstPageId = $this->pageService->findFirstPageId($survey->getSurveyId(), $survey->getVersion());
        }

        if ($firstPageId && $this->isPageEmpty($firstPageId)) {
            $firstPageId = $this->getNextPageId($firstPageId);
        }

        return $firstPageId;
    }

    public function getNextPageId(int $pageId): ?int
    {
        $survey = $this->surveyHandler->getSurvey();
        $nextPageId = $this->randomizedResponseSession->getNextPageId($pageId);
        if (!$nextPageId) {
            $nextPageId = $this->pageService->findNextPageId($survey->getSurveyId(), $survey->getVersion(), $pageId);
        }

        if ($nextPageId && $this->isPageEmpty($nextPageId)) {
            $nextPageId = $this->getNextPageId($nextPageId);
        }

        return $nextPageId;
    }

    public function getLastPageId():? int
    {
        $lastPageId = $this->randomizedResponseSession->getLastPageId();
        if (!$lastPageId) {
            $survey = $this->surveyHandler->getSurvey();
            $lastPageId = $this->pageService->findLastPageId($survey->getSurveyId(), $survey->getVersion());
        }

        return $lastPageId;
    }

    public function findPage(int $surveyId, int $version, int $pageId):? Document\Page
    {
        return $this->pageService->findPage($surveyId, $version, $pageId);
    }

    private function isPageEmpty(int $pageId): bool
    {
        $survey = $this->surveyHandler->getSurvey();
        $page = $this->pageService->findPage($survey->getSurveyId(), $survey->getVersion(), $pageId);

        if (!$page) {
            return true;
        }

        if ($page->getVisibleQuestions()->isEmpty() && !$page->hasContent()) {
            return true;
        }

        if ($this->responseHandler->hasResponse()) {
            $response = $this->responseHandler->getResponse();
        } else {
            $response = $this->responseHandler->getSaved($survey->getSurveyId());
        }

        if ($response) {
            $questions = $this->conditionService->filterQuestionsByShowCondition($page->getQuestions(), $response);
            if (!$questions || $questions->isEmpty()) {
                return true;
            }
        }

        return false;
    }


}
