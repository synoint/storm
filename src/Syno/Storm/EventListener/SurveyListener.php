<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Services\SurveyRequest;

class SurveyListener implements EventSubscriberInterface
{
    /** @var SurveyRequest */
    private $surveyRequestService;
    /** @var RouterInterface */
    private $router;

    /**
     * @param SurveyRequest   $surveyRequestService
     * @param RouterInterface $router
     */
    public function __construct(SurveyRequest $surveyRequestService, RouterInterface $router)
    {
        $this->surveyRequestService = $surveyRequestService;
        $this->router               = $router;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if ($this->isApiRoute($request)) {
            return;
        }

        if (!$this->surveyRequestService->hasSurveyId($request)) {
            return;
        }

        $survey = $this->surveyRequestService->fetchSurvey($request);
        if ($survey) {
            $this->surveyRequestService->setSurvey($request, $survey);
            return;
        }

        $event->setResponse(new RedirectResponse($this->router->generate('survey.unavailable')));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 8],
        ];
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isApiRoute(Request $request)
    {
        return false !== strpos($request->attributes->get('_route'), 'storm_api');
    }
}
