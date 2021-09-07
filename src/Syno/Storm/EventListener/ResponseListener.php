<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\RequestHandler;
use Syno\Storm\Services\ResponseEventLogger;
use Syno\Storm\Services\ResponseState;
use Syno\Storm\Services\SurveyEventLogger;
use Syno\Storm\Traits\RouteAware;


class ResponseListener implements EventSubscriberInterface
{
    use RouteAware;

    private RequestHandler\Survey   $surveyRequestHandler;
    private RequestHandler\Page     $pageRequestHandler;
    private RequestHandler\Response $responseRequestHandler;
    private ResponseEventLogger     $responseEventLogger;
    private ResponseState           $responseState;
    private RouterInterface         $router;
    private SurveyEventLogger       $surveyEventLogger;

    public function __construct(
        RequestHandler\Survey $surveyRequestHandler,
        RequestHandler\Page $pageRequestHandler,
        RequestHandler\Response $responseRequestHandler,
        ResponseEventLogger $responseEventLogger,
        SurveyEventLogger $surveyEventLogger,
        RouterInterface $router
    ) {
        $this->surveyRequestHandler   = $surveyRequestHandler;
        $this->pageRequestHandler     = $pageRequestHandler;
        $this->responseRequestHandler = $responseRequestHandler;
        $this->responseEventLogger    = $responseEventLogger;
        $this->surveyEventLogger      = $surveyEventLogger;
        $this->router                 = $router;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$event->isMasterRequest() || !$this->surveyRequestHandler->hasSurvey($request)) {
            return;
        }

        $survey     = $this->surveyRequestHandler->getSurvey($request);
        $responseId = $this->responseRequestHandler->getResponseId($request, $survey->getSurveyId());

        if (!empty($responseId)) {
            $surveyResponse = $this->responseRequestHandler->getSavedResponse($survey->getSurveyId(), $responseId);

            if ($surveyResponse) {
                $redirectResponse = $this->responseState->redirectOnFinishedResponseAndWrongUrl(
                    $surveyResponse, $request
                );
                if ($redirectResponse) {
                    $event->setResponse($redirectResponse);
                    return;
                }

                /**
                 * If there's a mismatch between the current survey version and the one that was used before,
                 * let's replace the current version of survey in the request with the previously started one
                 */
                if ($surveyResponse->getSurveyVersion() !== $survey->getVersion()) {
                    $previousSurvey = $this->surveyRequestHandler->findSavedBySurveyIdAndVersion(
                        $surveyResponse->getSurveyId(),
                        $surveyResponse->getSurveyVersion()
                    );

                    // version no longer exists - log & restart the session
                    if (!$previousSurvey) {
                        $this->responseState->switchSurveyVersion($survey, $surveyResponse);
                        $event->setResponse(
                            $this->getRedirectToEntrance($survey->getSurveyId(), $request->query->all())
                        );
                        return;
                    }

                    $this->surveyRequestHandler->setSurvey($request, $previousSurvey);
                    $survey = $previousSurvey;
                }

                /**
                 * Resume survey
                 */
                if ($this->isSurveyEntrance($request->attributes->get('_route'))) {
                    if ($this->responseRequestHandler->hasModeChanged($request, $surveyResponse->getMode())) {
                        // if mode have changed clear session and reload page
                        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_MODE_CHANGED, $surveyResponse);

                        $this->clearResponse($request, $surveyResponse);

                        $response = new RedirectResponse($request->getUri());
                        $event->setResponse($response);
                        return;
                    }

                    $surveyResponse = $this->responseRequestHandler->addUserAgent($request, $surveyResponse);
                    if ($surveyResponse->getPageId() && null !== $survey->getPage($surveyResponse->getPageId())) {
                        $event->setResponse(
                            $this->getRedirectToPage($survey->getSurveyId(), $surveyResponse->getPageId())
                        );
                        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_RESUMED, $surveyResponse);
                    } else {
                        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_ENTERED, $surveyResponse);
                    }
                }

                $this->responseRequestHandler->setResponse($request, $surveyResponse);
                return;
            }
        }

        /**
         * New response
         */

        // we have no response and it's not entrance - redirect to entrance
        if ($this->isSurveyEntrance($request->attributes->get('_route'))) {
            $this->createNewSurveyResponse($request, $survey);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 4]
        ];
    }

    private function getRedirectToEntrance(int $surveyId, array $queryParams): RedirectResponse
    {
        return new RedirectResponse(
            $this->router->generate(
                'survey.index',
                array_merge(
                    ['surveyId' => $surveyId],
                    $queryParams
                )
            )
        );
    }

    private function getRedirectToPage(int $surveyId, int $pageId): RedirectResponse
    {
        return new RedirectResponse(
            $this->router->generate('page.index', [
                'surveyId' => $surveyId,
                'pageId' => $pageId
            ])
        );
    }
}
