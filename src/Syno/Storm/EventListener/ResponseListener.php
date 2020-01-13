<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Event\SurveyCompleted;
use Syno\Storm\Services\PageRequest;
use Syno\Storm\Services\ResponseRequest;
use Syno\Storm\Services\SurveyRequest;


class ResponseListener implements EventSubscriberInterface
{
    /** @var PageRequest */
    private $pageRequestService;

    /** @var ResponseRequest */
    private $responseRequestService;

    /** @var SurveyRequest */
    private $surveyRequestService;

    /** @var RouterInterface */
    private $router;

    /**
     * @param PageRequest     $pageRequestService
     * @param ResponseRequest $responseRequestService
     * @param SurveyRequest   $surveyRequestService
     * @param RouterInterface $router
     */
    public function __construct(
        PageRequest $pageRequestService,
        ResponseRequest $responseRequestService,
        SurveyRequest $surveyRequestService,
        RouterInterface $router
    )
    {
        $this->pageRequestService     = $pageRequestService;
        $this->responseRequestService = $responseRequestService;
        $this->surveyRequestService   = $surveyRequestService;
        $this->router                 = $router;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if (!$this->surveyRequestService->hasSurvey($request)) {
            return;
        }

        $survey = $this->surveyRequestService->getSurvey($request);
        $responseId = $this->responseRequestService->getResponseId($request, $survey->getSurveyId());
        if (!empty($responseId)) {
            $surveyResponse = $this->responseRequestService->getSavedResponse($survey->getSurveyId(), $responseId);
            if ($surveyResponse) {

                if ($surveyResponse->isCompleted() && !$this->isSurveyCompletionPage($request)) {
                    $response = new RedirectResponse(
                        $this->router->generate('survey.complete', ['surveyId' => $survey->getSurveyId()])
                    );
                    $this->responseRequestService->clearResponse($request, $response, $survey->getSurveyId());
                    $event->setResponse($response);
                    return;
                }

                /**
                 * If there's a mismatch between the current survey version and the one that was used before,
                 * let's replace the current version of survey in the request with the previously started one
                 */
                if ($surveyResponse->getSurveyVersion() !== $survey->getVersion()) {
                    $previousSurvey = $this->surveyRequestService->findSavedBySurveyIdAndVersion(
                        $surveyResponse->getSurveyId(),
                        $surveyResponse->getSurveyVersion()
                    );
                    if (!$previousSurvey) {
                        $event->setResponse(new RedirectResponse($this->router->generate('survey.unavailable')));
                        return;
                    }
                    $this->surveyRequestService->setSurvey($request, $previousSurvey);
                    $survey = $previousSurvey;
                }

                /**
                 * Resume survey
                 */
                if ($this->isSurveyEntrance($request) &&
                    !empty($surveyResponse->getPageId()) &&
                    null !== $survey->getPage($surveyResponse->getPageId())
                ) {
                    $event->setResponse(
                        new RedirectResponse(
                            $this->router->generate('page.index', [
                                'surveyId' => $survey->getSurveyId(),
                                'pageId'   => $surveyResponse->getPageId()
                            ])
                        )
                    );
                }

                $this->responseRequestService->setResponse($request, $surveyResponse);
                return;
            }
        }


        /**
         * New response
         */

        // let's not initiate response on a completion page
        if ($this->isSurveyCompletionPage($request)) {
            return;
        }

        if (!$this->isSurveyEntrance($request)) {
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

        $surveyResponse = $this->responseRequestService->getNewResponse($request, $survey);
        $surveyResponse->setHiddenValues($this->responseRequestService->extractHiddenValues(
            $survey->getHiddenValues(),
            $request
        ));

        $this->responseRequestService->setResponse($request, $surveyResponse);
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if (!$this->responseRequestService->hasResponse($request)) {
            return;
        }

        $surveyResponse = $this->responseRequestService->getResponse($request);
        if ($surveyResponse->isCompleted()) {
            $this->responseRequestService->clearResponse($request, $event->getResponse(), $surveyResponse->getSurveyId());
            return;
        }

        if ($this->pageRequestService->hasPage($request)) {
            $page = $this->pageRequestService->getPage($request);
            $surveyResponse->setPageId($page->getPageId());
        }

        $surveyResponse->addUserAgent(
            IPUtils::anonymize($request->getClientIp()),
            $request->headers->get('User-Agent')
        );

        $this->responseRequestService->saveResponse($surveyResponse);

        $idFromSession = $this->responseRequestService->getResponseIdFromSession($request, $surveyResponse->getSurveyId());
        if ($surveyResponse->getResponseId() !== $idFromSession) {
            $this->responseRequestService->saveResponseIdInSession($request, $surveyResponse);
        }

        $idFromCookie = $this->responseRequestService->getResponseIdFromCookie($request, $surveyResponse->getSurveyId());
        if ($surveyResponse->getResponseId() !== $idFromCookie) {
            $event->getResponse()->headers->setCookie(
                $this->responseRequestService->getResponseIdCookie($surveyResponse)
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

        if (!$this->responseRequestService->hasResponse($request)) {
            return;
        }

        $surveyResponse = $this->responseRequestService->getResponse($request);
        $surveyResponse->setCompleted(true);
        $this->responseRequestService->saveResponse($surveyResponse);
    }



    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 4],
            KernelEvents::RESPONSE => 'onKernelResponse',
            SurveyCompleted::class => 'onSurveyCompleted'
        ];
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isSurveyEntrance(Request $request): bool
    {
        return in_array($request->attributes->get('_route'), ['survey.index', 'survey.test', 'survey.debug']);
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


}
