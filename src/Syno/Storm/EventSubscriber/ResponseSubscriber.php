<?php

namespace Syno\Storm\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\RequestHandler;
use Syno\Storm\Services\ResponseSession;
use Syno\Storm\Traits\RouteAware;


class ResponseSubscriber implements EventSubscriberInterface
{
    use RouteAware;

    private RequestHandler\Survey   $surveyHandler;
    private RequestHandler\Response $responseHandler;
    private ResponseSession         $responseSession;
    private RouterInterface         $router;

    public function __construct(
        RequestHandler\Survey   $surveyHandler,
        RequestHandler\Response $responseHandler,
        ResponseSession         $responseSession,
        RouterInterface         $router
    )
    {
        $this->surveyHandler   = $surveyHandler;
        $this->responseHandler = $responseHandler;
        $this->responseSession = $responseSession;
        $this->router          = $router;
    }

    public function setResponse(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->surveyHandler->hasSurvey()) {
            return;
        }

        $survey   = $this->surveyHandler->getSurvey();
        $response = $this->responseHandler->getSaved($survey->getSurveyId());
        if ($response) {
            $this->responseHandler->setResponse($response);
        }
    }

    /**
     * If the survey response is in final state (screenout, complete, etc.)
     * but on the wrong URL this method redirects client to the proper location
     * and stops further event propagation
     *
     * @param RequestEvent $event
     */
    public function handleFinishedResponse(RequestEvent $event)
    {
        if (!$this->responseHandler->hasResponse()) {
            return;
        }

        $redirect = $this->responseSession->isFinishedButLost($this->surveyHandler->getSurvey(), $event->getRequest());
        if ($redirect) {
            $event->setResponse($redirect);
        }
    }

    /**
     * If there's a mismatch between the currently loaded survey version
     * and the one that the response has been started on,
     * we reload the previous version as current
     *
     * @param RequestEvent $event
     */
    public function handleSurveyVersionChange(RequestEvent $event)
    {
        if (!$this->responseHandler->hasResponse()) {
            return;
        }

        $survey   = $this->surveyHandler->getSurvey();
        $response = $this->responseHandler->getResponse();

        if ($response->getSurveyVersion() === $survey->getVersion()) {
            return;
        }

        $previousSurvey = $this->surveyHandler->findSaved(
            $response->getSurveyId(),
            $response->getSurveyVersion()
        );

        if ($previousSurvey) {
            $this->surveyHandler->setSurvey($previousSurvey);

            return;
        }

        // version no longer exists - reset the response & restart the session
        $redirect = $this->responseSession->switchSurveyVersion($survey, $event->getRequest()->query->all());
        $event->setResponse($redirect);
    }

    /**
     * If survey response mode (live, test, debug) has changed clear the session and reload the page
     *
     * @param RequestEvent $event
     */
    public function handleModeChange(RequestEvent $event)
    {
        if (!$this->responseHandler->hasResponse()) {
            return;
        }

        if (!$this->isSurveyEntrance($event->getRequest())) {
            return;
        }

        $redirect = $this->responseSession->redirectOnModeChange($event->getRequest());
        if ($redirect) {
            $event->setResponse($redirect);
        }
    }

    /**
     * If we already have a response on the entrance page, redirect to the last known page
     *
     * @param RequestEvent $event
     */
    public function handleSurveyResume(RequestEvent $event)
    {
        if (!$this->responseHandler->hasResponse()) {
            return;
        }

        if (!$this->isSurveyEntrance($event->getRequest())) {
            return;
        }

        $redirect = $this->responseSession->resumeSurvey($this->surveyHandler->getSurvey());

        if ($redirect) {
            $event->setResponse($redirect);
        }
    }

    /**
     * If we have no survey response and it's not an entrance page, redirect to entrance
     *
     * @param RequestEvent $event
     */
    public function handleLostVisitor(RequestEvent $event)
    {
        if (!$this->surveyHandler->hasSurvey()) {
            return;
        }

        if ($this->responseHandler->hasResponse()) {
            return;
        }

        if ($this->isSurveyEntrance($event->getRequest())) {
            return;
        }

        $redirect = new RedirectResponse(
            $this->router->generate(
                $this->getLiveEntranceRoute(),
                array_merge(
                    ['surveyId' => $this->surveyHandler->getSurvey()->getSurveyId()],
                    $event->getRequest()->query->all()
                )
            )
        );
        $event->setResponse($redirect);
    }

    /**
     * If we don't have a response on the entrance page, create it here
     *
     * @param RequestEvent $event
     */
    public function createResponse(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->responseHandler->hasResponse()) {
            return;
        }

        if (!$this->isSurveyEntrance($event->getRequest())) {
            return;
        }

        if (!$this->surveyHandler->hasSurvey()) {
            return;
        }

        $this->responseSession->createResponse($this->surveyHandler->getSurvey());
    }

    public function logUserAgent(RequestEvent $event)
    {
        if (!$this->responseHandler->hasResponse()) {
            return;
        }

        // log only upon entrance
        if (!$this->isSurveyEntrance($event->getRequest())) {
            return;
        }

        $this->responseHandler->addUserAgent($this->responseHandler->getResponse());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['setResponse', 8],
                ['handleFinishedResponse', 7],
                ['handleSurveyVersionChange', 6],
                ['handleModeChange', 5],
                ['handleSurveyResume', 4],
                ['handleLostVisitor', 3],
                ['createResponse', 2],
                ['logUserAgent'],
            ]
        ];
    }

}
