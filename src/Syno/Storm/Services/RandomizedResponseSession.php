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

    private ?Document\SurveyPath $currentPath          = null;
    private ?bool                $responseIsRandomized = null;

    public function __construct(RequestHandler\Response $responseHandler, SurveyPath $surveyPathService)
    {
        $this->responseHandler   = $responseHandler;
        $this->surveyPathService = $surveyPathService;
    }

    public function isRandomized(): bool
    {
        if (null === $this->responseIsRandomized) {
            $this->responseIsRandomized = false;
            if ($this->responseHandler->hasResponse()) {
                $response = $this->responseHandler->getResponse();
                if ($response->getSurveyPathId()) {
                    /** @var Document\SurveyPath $surveyPath */
                    $surveyPath = $this->surveyPathService->findOneById($response->getSurveyPathId());
                    if ($surveyPath) {
                        $this->currentPath = $surveyPath;
                        $this->responseIsRandomized = true;
                    }
                }
            }
        }

        return $this->responseIsRandomized;
    }

    public function getFirstPageId():? int
    {
        return $this->currentPath->getFirstPageId();
    }

    public function getNextPageId(int $pageId):? int
    {
        return $this->currentPath->getNextPageId($pageId);
    }

    public function getLastPageId():? int
    {
        return $this->currentPath->getLastPageId();
    }
}
