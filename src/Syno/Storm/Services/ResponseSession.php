<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Syno\Storm\Document;
use Syno\Storm\Event\ResponseComplete;
use Syno\Storm\RequestHandler;
use Syno\Storm\Traits\RouteAware;

class ResponseSession
{
    use RouteAware;

    private Condition                $conditionService;
    private EventDispatcherInterface $dispatcher;
    private Page                     $pageService;
    private RequestHandler\Response  $responseHandler;
    private ResponseEventLogger      $responseEventLogger;
    private ResponseRedirector       $responseRedirector;
    private SurveyEventLogger        $surveyEventLogger;
    private SurveyPath               $surveyPathService;

    public function __construct(
        Condition                $conditionService,
        EventDispatcherInterface $dispatcher,
        Page                     $pageService,
        RequestHandler\Response  $responseHandler,
        ResponseEventLogger      $responseEventLogger,
        ResponseRedirector       $responseRedirector,
        SurveyEventLogger        $surveyEventLogger,
        SurveyPath               $surveyPathService
    )
    {
        $this->conditionService    = $conditionService;
        $this->dispatcher          = $dispatcher;
        $this->pageService         = $pageService;
        $this->responseHandler     = $responseHandler;
        $this->responseEventLogger = $responseEventLogger;
        $this->responseRedirector  = $responseRedirector;
        $this->surveyEventLogger   = $surveyEventLogger;
        $this->surveyPathService   = $surveyPathService;
    }


    public function isFinishedButLost(Document\Survey $survey, Request $request): ?RedirectResponse
    {
        $response = $this->responseHandler->getResponse();

        if ($response->isCompleted() && !$this->isSurveyCompletePage($request)) {
            return $this->responseRedirector->complete($survey, $response);
        }
        if ($response->isScreenedOut() && !$this->isSurveyScreenOutPage($request)) {
            return $this->responseRedirector->screenOut($survey, $response);
        }
        if ($response->isQualityScreenedOut() && !$this->isSurveyQualityScreenOutPage($request)) {
            return $this->responseRedirector->qualityScreenOut($survey, $response);
        }
        if ($response->isQuotaFull() && !$this->isSurveyQuotaFullPage($request)) {
            return $this->responseRedirector->quotaFull($survey);
        }

        return null;
    }

    public function switchSurveyVersion(Document\Survey $survey, array $params): RedirectResponse
    {
        $response = $this->responseHandler->getResponse();
        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_VERSION_UNAVAILABLE, $response);

        $response
            ->setSurveyVersion($survey->getVersion())
            ->setPageId()
            ->setPageCode(null)
            ->clearAnswers();

        $this->responseHandler->saveResponse($response);

        $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_CLEARED, $response);

        return $this->responseRedirector->surveyEntrance(
            $response->getSurveyId(),
            $response->getMode(),
            $params
        );
    }

    public function redirectOnModeChange(Request $request): ?RedirectResponse
    {
        $redirect = null;
        $response = $this->responseHandler->getResponse();
        if ($this->responseHandler->hasModeChanged($response->getMode())) {
            $this->responseEventLogger->log(ResponseEventLogger::SURVEY_MODE_CHANGED, $response);
            $this->responseHandler->clearResponse();
            $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_CLEARED, $response);

            $redirect = new RedirectResponse($request->getUri());
        }

        return $redirect;
    }

    public function resumeSurvey(): ?RedirectResponse
    {
        $response = $this->responseHandler->getResponse();

        if ($response->getPageId()) {
            $page = $this->pageService->findPage(
                $response->getSurveyId(),
                $response->getSurveyVersion(),
                $response->getPageId()
            );
            if ($page) {
                $this->responseEventLogger->log(ResponseEventLogger::SURVEY_RESUMED, $response);
                $this->responseHandler->saveResponse($response, true);

                return $this->responseRedirector->page($response->getSurveyId(), $response->getPageId());
            }
        }

        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_ENTERED, $response);

        return null;
    }

    public function createResponse(Document\Survey $survey)
    {
        $response = $this->responseHandler->getNew($survey);

        $surveyPath = $this->surveyPathService->getRandomSurveyPath($survey);
        if ($surveyPath) {
            $response->setSurveyPathId($surveyPath->getSurveyPathId());
        }

        $response->setParameters(
            $this->responseHandler->extractParameters(
                $survey->getParameters()
            )
        );

        $this->responseHandler->saveResponse($response, true);

        $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_CREATED, $response);
        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_ENTERED, $response);
    }

    public function saveAnswers(Document\Response $response, Collection $answers): Document\Response
    {
        $response->saveAnswers($answers);
        $this->responseHandler->saveResponse($response);

        $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_SAVED, $response, $answers);

        return $response;
    }

    public function saveProgress(Document\Page $page)
    {
        $response = $this->responseHandler->getResponse();
        if ($response->getPageId() !== $page->getPageId()) {
            $response->setPageId($page->getPageId());
            $response->setPageCode($page->getCode());
            $this->responseHandler->saveResponse($response);

            $this->responseEventLogger->log(ResponseEventLogger::PAGE_ENTERED, $response);
        }
    }

    public function complete(Document\Survey $survey): RedirectResponse
    {
        $response = $this->responseHandler->getResponse();

        if ($survey->getSurveyScreenoutCondition() && $survey->getSurveyScreenoutCondition()->getRule()) {
            $surveyIsScreenoutable = $this->conditionService->applySurveyConditionRule(
                $this->responseHandler->getResponse(),
                $survey->getSurveyScreenoutCondition()->getRule()
            );

            if ($surveyIsScreenoutable) {
                $this->responseEventLogger->log(ResponseEventLogger::SURVEY_SCREENOUTED_ON_SCREENOUT_CONDITION, $response);

                return $this->screenOut($survey);
            }
        }

        if ($survey->getSurveyCompleteCondition() && $survey->getSurveyCompleteCondition()->getRule()) {
            $surveyIsCompletable = $this->conditionService->applySurveyConditionRule(
                $this->responseHandler->getResponse(),
                $survey->getSurveyCompleteCondition()->getRule()
            );

            if (!$surveyIsCompletable) {
                $this->responseEventLogger->log(ResponseEventLogger::SURVEY_SCREENOUTED_ON_COMPLETE_CONDITION, $response);

                return $this->screenOut($survey);
            }
        }

        $response->setCompleted(true);

        $this->responseHandler->saveResponse($response);

        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_COMPLETED, $response);

        $this->dispatcher->dispatch(new ResponseComplete($survey, $response));

        return $this->responseRedirector->complete($survey, $response);
    }

    public function qualityScreenOut(Document\Survey $survey): RedirectResponse
    {
        $response = $this->responseHandler->getResponse();
        $response->setQualityScreenedOut(true);
        $this->responseHandler->saveResponse($response);

        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_QUALITY_SCREENOUTED, $response);

        return $this->responseRedirector->qualityScreenOut($survey, $response);
    }


    public function screenOut(Document\Survey $survey): RedirectResponse
    {
        $response = $this->responseHandler->getResponse();
        $response->setScreenedOut(true);
        $this->responseHandler->saveResponse($response);

        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_SCREENOUTED, $response);

        return $this->responseRedirector->screenOut($survey, $response);
    }

    public function jump(Document\Survey $survey, Document\JumpToCondition $jump): RedirectResponse
    {
        $response = $this->responseHandler->getResponse();

        if (Document\JumpToCondition::DESTINATION_TYPE_END_OF_SURVEY == $jump->getDestinationType()) {
            $this->responseEventLogger->log(ResponseEventLogger::JUMPED_TO_END_OF_SURVEY, $response);

            return $this->complete($survey);
        }

        if (Document\JumpToCondition::DESTINATION_TYPE_QUESTION == $jump->getDestinationType()) {
            $this->responseEventLogger->log(ResponseEventLogger::JUMPED_TO_PAGE, $response);
            $jumpToPageId = $this->pageService->findPageIdByQuestionId(
                $survey->getSurveyId(),
                $survey->getVersion(),
                $jump->getDestination()
            );

            if ($jumpToPageId) {
                return $this->responseRedirector->page($survey->getSurveyId(), $jumpToPageId);
            }

            return $this->responseRedirector->pageUnavailable();
        }

        throw new \UnexpectedValueException('Unknown jump type');
    }

    public function redirectToPage(int $surveyId, int $pageId): RedirectResponse
    {
        return $this->responseRedirector->page($surveyId, $pageId);
    }

    public function redirectToPageUnavailable(): RedirectResponse
    {
        $response = $this->responseHandler->getResponse();
        if ($response) {
            $this->responseEventLogger->log(ResponseEventLogger::SURVEY_PAGE_UNAVAILABLE, $response);
        }

        return $this->responseRedirector->pageUnavailable();
    }

    public function answeredWithErrors()
    {
        $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_ERROR, $this->responseHandler->getResponse());
    }

    public function redirectToSessionCookieCheck(int $surveyId): RedirectResponse
    {
        return $this->responseRedirector->sessionCookieCheck($surveyId);
    }
}
