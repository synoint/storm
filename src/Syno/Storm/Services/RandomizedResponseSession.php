<?php

namespace Syno\Storm\Services;

use Syno\Storm\Document;
use Syno\Storm\RequestHandler;
use Syno\Storm\Traits\RouteAware;

class RandomizedResponseSession
{
    use RouteAware;

    private RequestHandler\Response  $responseHandler;
    private SurveyPath               $surveyPathService;

    public function __construct(RequestHandler\Response $responseHandler, SurveyPath $surveyPathService)
    {
        $this->responseHandler   = $responseHandler;
        $this->surveyPathService = $surveyPathService;
    }

    public function getFirstPageId():? int
    {
        $result = null;
        if ($this->responseHandler->hasResponse()) {
            $response = $this->responseHandler->getResponse();
            if ($response->getSurveyPathId()) {
                /** @var Document\SurveyPath $surveyPath */
                $surveyPath = $this->surveyPathService->findOneById($response->getSurveyPathId());
                if ($surveyPath) {
                    $result = $surveyPath->getFirstPageId();
                }
            }
        }

        return $result;
    }

    public function getNextPageId(int $pageId):? int
    {
        $result = null;
        if ($this->responseHandler->hasResponse()) {
            $response = $this->responseHandler->getResponse();
            if ($response->getSurveyPathId()) {
                /** @var Document\SurveyPath $surveyPath */
                $surveyPath = $this->surveyPathService->findOneById($response->getSurveyPathId());
                if ($surveyPath) {
                    $result = $surveyPath->getNextPageId($pageId);
                }
            }
        }

        return $result;
    }

    public function getLastPageId():? int
    {
        $result = null;
        if ($this->responseHandler->hasResponse()) {
            $response = $this->responseHandler->getResponse();
            if ($response->getSurveyPathId()) {
                /** @var Document\SurveyPath $surveyPath */
                $surveyPath = $this->surveyPathService->findOneById($response->getSurveyPathId());
                if ($surveyPath) {
                    $result = $surveyPath->getLastPageId();
                }
            }
        }

        return $result;
    }
}
