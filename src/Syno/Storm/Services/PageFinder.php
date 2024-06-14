<?php

namespace Syno\Storm\Services;

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
        Condition $conditionService,
        Page $pageService,
        RandomizedResponseSession $randomizedResponseSession,
        RequestHandler\Response $responseHandler,
        RequestHandler\Survey $surveyHandler
    ) {
        $this->conditionService          = $conditionService;
        $this->pageService               = $pageService;
        $this->randomizedResponseSession = $randomizedResponseSession;
        $this->responseHandler           = $responseHandler;
        $this->surveyHandler             = $surveyHandler;
    }
    
    
    public function getFirstPageId(): ?int
    {
        if ($this->randomizedResponseSession->isRandomized()) {
            $firstPageId = $this->randomizedResponseSession->getFirstPageId();
        } else {
            $survey      = $this->surveyHandler->getSurvey();
            $firstPageId = $this->pageService->findFirstPageId($survey->getSurveyId(), $survey->getVersion());
        }
        
        if ($firstPageId && $this->isPageEmpty($firstPageId)) {
            $firstPageId = $this->getNextPageId($firstPageId);
        }
        
        return $firstPageId;
    }
    
    public function getNextPageId(int $pageId): ?int
    {
        if ($this->randomizedResponseSession->isRandomized()) {
            $nextPageId = $this->randomizedResponseSession->getNextPageId($pageId);
        } else {
            $survey     = $this->surveyHandler->getSurvey();
            $nextPageId = $this->pageService->findNextPageId($survey->getSurveyId(), $survey->getVersion(), $pageId);
        }
        
        if ($nextPageId && $this->isPageEmpty($nextPageId)) {
            $nextPageId = $this->getNextPageId($nextPageId);
        }
        
        return $nextPageId;
    }
    
    public function getLastPageId(): ?int
    {
        if ($this->randomizedResponseSession->isRandomized()) {
            return $this->randomizedResponseSession->getLastPageId();
        }
        
        $survey = $this->surveyHandler->getSurvey();
        
        return $this->pageService->findLastPageId($survey->getSurveyId(), $survey->getVersion());
    }
    
    public function findPage(int $surveyId, int $version, int $pageId): ?Document\Page
    {
        return $this->pageService->findPage($surveyId, $version, $pageId);
    }
    
    private function isPageEmpty(int $pageId): bool
    {
        $survey = $this->surveyHandler->getSurvey();
        $page   = $this->pageService->findPage($survey->getSurveyId(), $survey->getVersion(), $pageId);
        
        if (!$page) {
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
        
        if ($page->hasContent()) {
            return false;
        }
        
        if ($page->getQuestions()->isEmpty() || !$page->getVisibleQuestions()->count()) {
            return true;
        }
        
        return false;
    }
}
