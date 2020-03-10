<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Controller\PageController;
use Syno\Storm\Event\SurveyCompleted;
use Syno\Storm\Document;
use Syno\Storm\RequestHandler;
use Syno\Storm\Services\ResponseEventLogger;
use Syno\Storm\Services\SurveyEventLogger;
use Syno\Storm\Traits\RouteAware;


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

                /**
                 * If there's a mismatch between the current survey version and the one that was used before,
                 * let's replace the current version of survey in the request with the previously started one
                 */
                if ($surveyResponse->getSurveyVersion() !== $survey->getVersion()) {
                    $previousSurvey = $this->surveyRequestHandler->findSavedBySurveyIdAndVersion(
                        $surveyResponse->getSurveyId(),
                        $surveyResponse->getSurveyVersion()
                    );
                    if (!$previousSurvey) {

                        $this->responseEventLogger->log(
                            ResponseEventLogger::SURVEY_VERSION_UNAVAILABLE,
                            $surveyResponse
                        );

                        $surveyResponse
                            ->setSurveyVersion($survey->getVersion())
                            ->setPageId($survey->getPages()->first()->getPageId())
                            ->clearAnswers();

                        $this->responseEventLogger->log(
                            ResponseEventLogger::ANSWERS_CLEARED,
                            $surveyResponse
                        );

                    } else {
                        $this->surveyRequestHandler->setSurvey($request, $previousSurvey);
                        $survey = $previousSurvey;
                    }
                }

                /**
                 * Resume survey
                 */
                if ($this->isSurveyEntrance($request->attributes->get('_route'))) {

                    $surveyResponse = $this->responseRequestHandler->addUserAgent($request, $surveyResponse);

                    if ($surveyResponse->getPageId() && null !== $survey->getPage($surveyResponse->getPageId())) {
                        $event->setResponse(
                            new RedirectResponse(
                                $this->router->generate('page.index', [
                                    'surveyId' => $survey->getSurveyId(),
                                    'pageId'   => $surveyResponse->getPageId()
                                ])
                            )
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

        // let's not re-initiate response on a completion page
        if ($this->isSurveyCompletionPage($request)) {
            return;
        }

        // we have no response and it's not entrance - redirect to entrance
        if (!$this->isSurveyEntrance($request->attributes->get('_route'))) {
            $event->setResponse(
                new RedirectResponse(
                    $this->router->generate(
                        'survey.index',
                        array_merge(
                            ['surveyId' => $survey->getSurveyId()],
                            $request->query->all()
                        )
                    )
                )
            );
            return;
        }

        $this->createNewSurveyResponse($request, $survey);
    }

    /**
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();

        if (!$this->responseRequestHandler->hasResponse($request)) {
            return;
        }

        if (!$this->pageRequestHandler->hasPage($request)) {
            return;
        }

        $controller = $event->getController();
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof PageController && 'GET' === $request->getMethod()) {
            $surveyResponse = $this->responseRequestHandler->getResponse($request);

            $page = $this->pageRequestHandler->getPage($request);
            $surveyResponse->setPageId($page->getPageId());

            $this->responseEventLogger->log(ResponseEventLogger::PAGE_ENTERED, $surveyResponse);
        }
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
            $this->responseRequestHandler->clearResponse($request);
            $this->responseRequestHandler->clearResponseIdInSession($request, $surveyResponse->getSurveyId());
            $this->responseRequestHandler->clearResponseIdCookie($event->getResponse(), $surveyResponse->getSurveyId());
            $request->getSession()->migrate(true);
            $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_CLEARED, $surveyResponse);
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

    /**
     * @param SurveyCompleted $event
     */
    public function onSurveyCompleted(SurveyCompleted $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();

        if (!$this->responseRequestHandler->hasResponse($request)) {
            return;
        }

        $surveyResponse = $this->responseRequestHandler->getResponse($request);
        $surveyResponse->setCompleted(true);
        $this->responseRequestHandler->saveResponse($surveyResponse);

        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_COMPLETED, $surveyResponse);
    }



    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST    => ['onKernelRequest', 4],
            KernelEvents::CONTROLLER => ['onKernelController'],
            KernelEvents::RESPONSE   => ['onKernelResponse'],
            SurveyCompleted::class   => ['onSurveyCompleted']
        ];
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

        switch ($surveyResponse->getMode()) {
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


}
