<?php

namespace Syno\Storm\EventListener;

use PhpParser\Comment\Doc;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Controller\PageController;
use Syno\Storm\Document;
use Syno\Storm\RequestHandler;
use Syno\Storm\Services\ResponseEventLogger;
use Syno\Storm\Services\SurveyEventLogger;
use Syno\Storm\Traits\RouteAware;
use Syno\Storm\Services;


class ResponseListener implements EventSubscriberInterface
{
    use RouteAware;

    /** @var RequestHandler\Survey */
    private $surveyRequestHandler;

    /** @var RequestHandler\Page */
    private $pageRequestHandler;

    /** @var RequestHandler\Response */
    private $responseRequestHandler;

    /** @var ResponseEventLogger */
    private $responseEventLogger;

    /** @var RouterInterface */
    private $router;

    /** @var SurveyEventLogger */
    private $surveyEventLogger;

    /**
     * @param RequestHandler\Survey   $surveyRequestHandler
     * @param RequestHandler\Page     $pageRequestHandler
     * @param RequestHandler\Response $responseRequestHandler
     * @param ResponseEventLogger     $responseEventLogger
     * @param SurveyEventLogger       $surveyEventLogger
     * @param RouterInterface         $router
     */
    public function __construct(
        RequestHandler\Survey $surveyRequestHandler,
        RequestHandler\Page $pageRequestHandler,
        RequestHandler\Response $responseRequestHandler,
        ResponseEventLogger $responseEventLogger,
        SurveyEventLogger $surveyEventLogger,
        RouterInterface $router
    )
    {
        $this->surveyRequestHandler   = $surveyRequestHandler;
        $this->pageRequestHandler     = $pageRequestHandler;
        $this->responseRequestHandler = $responseRequestHandler;
        $this->responseEventLogger    = $responseEventLogger;
        $this->surveyEventLogger      = $surveyEventLogger;
        $this->router                 = $router;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();

        if (!$event->isMasterRequest() || !$this->surveyRequestHandler->hasSurvey($request)) {
            return;
        }

        $survey = $this->surveyRequestHandler->getSurvey($request);
        $responseId = $this->responseRequestHandler->getResponseId($request, $survey->getSurveyId());
        if (!empty($responseId)) {
            $surveyResponse = $this->responseRequestHandler->getSavedResponse($survey->getSurveyId(), $responseId);

            if ($surveyResponse) {

                if ($surveyResponse->isCompleted() && !$this->isSurveyCompletionPage($request)) {
                    $response = new RedirectResponse(
                        $this->router->generate('survey.complete', ['surveyId' => $survey->getSurveyId()])
                    );
                    $event->setResponse($response);
                    return;
                }

                if ($surveyResponse->isScreenedOut() && !$this->isSurveyScreenoutPage($request)) {
                    $response = new RedirectResponse(
                        $this->router->generate('survey.screenout', ['surveyId' => $survey->getSurveyId()])
                    );
                    $event->setResponse($response);
                    return;
                }

                if ($surveyResponse->isQualityScreenedOut() && !$this->isSurveyQualityScreenoutPage($request)) {
                    $response = new RedirectResponse(
                        $this->router->generate('survey.quality_screenout', ['surveyId' => $survey->getSurveyId()])
                    );
                    $event->setResponse($response);
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

                        $this->responseEventLogger->log(
                            ResponseEventLogger::SURVEY_VERSION_UNAVAILABLE,
                            $surveyResponse
                        );

                        $surveyResponse
                            ->setSurveyVersion($survey->getVersion())
                            ->setPageId(null)
                            ->clearAnswers();

                        $this->responseRequestHandler->saveResponse($surveyResponse);

                        $this->responseEventLogger->log(
                            ResponseEventLogger::ANSWERS_CLEARED,
                            $surveyResponse
                        );
                        // we need to log this response again, because previous version is gone
                        $this->logResponse($surveyResponse, $survey);

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

                    if ($this->responseRequestHandler->hasModeChanged($request, $surveyResponse->getMode())){
                        // if mode have changed clear session and reload page
                        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_MODE_CHANGED, $surveyResponse);

                        $response = new RedirectResponse($request->getUri());
                        $this->clearResponseSession($request, $surveyResponse, $response);

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

        // let's not re-initiate response on a completion pages
        if ($this->isSurveyCompletionPage($request) ||
            $this->isSurveyScreenoutPage($request) ||
            $this->isSurveyQualityScreenoutPage($request)) {
            return;
        }

        // we have no response and it's not entrance - redirect to entrance
        if (!$this->isSurveyEntrance($request->attributes->get('_route'))) {
            $event->setResponse(
                $this->getRedirectToEntrance($survey->getSurveyId(), $request->query->all())
            );
            return;
        }

        $this->createNewSurveyResponse($request, $survey);
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if (!$this->responseRequestHandler->hasResponse($request)) {
            return;
        }

        $surveyResponse = $this->responseRequestHandler->getResponse($request);
        if ($surveyResponse->isCompleted()) {
            $this->clearResponseSession($request, $surveyResponse, $event->getResponse());
            return;
        }

        $this->responseRequestHandler->saveResponse($surveyResponse);

        $idFromSession = $this->responseRequestHandler->getResponseIdFromSession($request, $surveyResponse->getSurveyId());
        if ($surveyResponse->getResponseId() !== $idFromSession) {
            $this->responseRequestHandler->saveResponseIdInSession($request, $surveyResponse);
        }

        $idFromCookie = $this->responseRequestHandler->getResponseIdFromCookie($request, $surveyResponse->getSurveyId());
        if ($surveyResponse->getResponseId() !== $idFromCookie) {
            $event->getResponse()->headers->setCookie(
                $this->responseRequestHandler->getResponseIdCookie($surveyResponse)
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST    => ['onKernelRequest', 4],
            KernelEvents::RESPONSE   => ['onKernelResponse'],
        ];
    }

    private function clearResponseSession(Request $request, Document\Response $surveyResponse, HttpResponse $eventResponse)
    {
        $this->responseRequestHandler->clearResponse($request);
        $this->responseRequestHandler->clearResponseIdInSession($request, $surveyResponse->getSurveyId());
        $this->responseRequestHandler->clearResponseIdCookie($eventResponse, $surveyResponse->getSurveyId());
        $request->getSession()->migrate(true);
        $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_CLEARED, $surveyResponse);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isSurveyCompletionPage(Request $request): bool
    {
        return $request->attributes->get('_route') === 'survey.complete';
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isSurveyScreenoutPage(Request $request): bool
    {
        return $request->attributes->get('_route') === 'survey.screenout';
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isSurveyQualityScreenoutPage(Request $request): bool
    {
        return $request->attributes->get('_route') === 'survey.quality_screenout';
    }

    /**
     * @param Request         $request
     * @param Document\Survey $survey
     */
    private function createNewSurveyResponse(Request $request, Document\Survey $survey)
    {
        $surveyResponse = $this->responseRequestHandler->getNewResponse($request, $survey);
        $surveyResponse = $this->responseRequestHandler->addUserAgent($request, $surveyResponse);
        $surveyResponse->setHiddenValues(
            $this->responseRequestHandler->extractHiddenValues(
                $survey->getHiddenValues(),
                $request
            )
        );
        $this->responseRequestHandler->saveResponse($surveyResponse);
        $this->responseRequestHandler->setResponse($request, $surveyResponse);

        $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_CREATED, $surveyResponse);
        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_ENTERED, $surveyResponse);
        $this->logResponse($surveyResponse, $survey);
    }

    /**
     * @param Document\Response $response
     * @param Document\Survey   $survey
     */
    private function logResponse(Document\Response $response, Document\Survey $survey)
    {
        switch ($response->getMode()) {
            case Document\Response::MODE_LIVE:
                $this->surveyEventLogger->log(SurveyEventLogger::LIVE_RESPONSE, $survey);
                break;
            case Document\Response::MODE_TEST:
                $this->surveyEventLogger->log(SurveyEventLogger::TEST_RESPONSE, $survey);
                break;
            case Document\Response::MODE_DEBUG:
                $this->surveyEventLogger->log(SurveyEventLogger::DEBUG_RESPONSE, $survey);
                break;
        }
    }

    /**
     * @param int   $surveyId
     * @param array $queryParams
     *
     * @return RedirectResponse
     */
    private function getRedirectToEntrance(int $surveyId, array $queryParams)
    {
        $redirectResponse = new RedirectResponse(
            $this->router->generate(
                'survey.index',
                array_merge(
                    ['surveyId' => $surveyId],
                    $queryParams
                )
            )
        );

        $redirectResponse->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');

        return $redirectResponse;
    }

    /**
     * @param int $surveyId
     * @param int $pageId
     *
     * @return RedirectResponse
     */
    private function getRedirectToPage(int $surveyId, int $pageId)
    {
        return new RedirectResponse(
            $this->router->generate('page.index', [
                'surveyId' => $surveyId,
                'pageId'   => $pageId
            ])
        );
    }


}
