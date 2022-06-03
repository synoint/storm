<?php

namespace Syno\Storm\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\RequestHandler;
use Syno\Storm\Services\ResponseSession;
use Syno\Storm\Services\ResponseSessionManager;
use Syno\Storm\Traits\RouteAware;
use Psr\Log\LoggerInterface;




class ResponseSubscriber implements EventSubscriberInterface
{
    use RouteAware;

    private LoggerInterface         $logger;
    private RequestHandler\Page     $pageHandler;
    private RequestHandler\Response $responseHandler;
    private RequestHandler\Survey   $surveyHandler;
    private ResponseSession         $responseSession;
    private ResponseSessionManager  $responseSessionManager;
    private RouterInterface         $router;

    public function __construct(
        LoggerInterface         $logger,
        RequestHandler\Page     $pageHandler,
        RequestHandler\Response $responseHandler,
        RequestHandler\Survey   $surveyHandler,
        ResponseSession         $responseSession,
        ResponseSessionManager  $responseSessionManager,
        RouterInterface         $router
    )
    {
        $this->logger                 = $logger;
        $this->pageHandler            = $pageHandler;
        $this->responseHandler        = $responseHandler;
        $this->surveyHandler          = $surveyHandler;
        $this->responseSession        = $responseSession;
        $this->responseSessionManager = $responseSessionManager;
        $this->router                 = $router;
    }

    public function setResponse(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->surveyHandler->hasSurvey()) {
            return;
        }

        $this->logger->debug(__FUNCTION__);

        $survey   = $this->surveyHandler->getSurvey();
        $response = $this->responseHandler->getSaved($survey->getSurveyId());
        if ($response) {
            $this->responseHandler->setResponse($response);
            return;
        }

        if ($this->pageHandler->hasPage()) {
            // we have page, but no response initiated, redirect to session cookie support check
            $event->setResponse($this->responseSession->redirectToSessionCookieCheck($survey->getSurveyId()));
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

        $this->logger->debug(__FUNCTION__);

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

        $this->logger->debug(__FUNCTION__);

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

        $this->logger->debug(__FUNCTION__);

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

        $this->logger->debug(__FUNCTION__);

        $redirect = $this->responseSession->resumeSurvey($this->surveyHandler->getSurvey());

        if ($redirect) {
            $event->setResponse($redirect);
        }
    }

    /**
     * If we don't have a response on the entrance page, create it here
     *
     * @param RequestEvent $event
     */
    public function createResponse(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
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

        $this->logger->debug(__FUNCTION__);

        $this->responseSession->createResponse($this->surveyHandler->getSurvey());
    }

    /**
     * @param RequestEvent $event
     */
    public function saveAnswersPassedInUrl(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->responseHandler->hasResponse()) {
            return;
        }

        if (!$this->isSurveyEntrance($event->getRequest())) {
            return;
        }

        $this->logger->debug(__FUNCTION__);

        $data = $event->getRequest()->query->get("p");
        if (!is_array($data)) {
            return;
        }

        $this->responseSessionManager->saveAnswers($data, $this->surveyHandler->getSurvey()->getQuestions());
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

        $this->logger->debug(__FUNCTION__);

        $this->responseHandler->addUserAgent($this->responseHandler->getResponse());
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Cache-Control', 'no-cache, no-store, no-transform, private, proxy-revalidate');
        $response->headers->set('Pragma', 'no-cache');
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
                ['createResponse', 2],
                ['saveAnswersPassedInUrl', 1],
                ['logUserAgent'],
            ],
            KernelEvents::RESPONSE => 'onKernelResponse'
        ];
    }

}
