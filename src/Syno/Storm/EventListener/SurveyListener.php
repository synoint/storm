<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\RequestHandler\Survey;

class SurveyListener implements EventSubscriberInterface
{
    /** @var Survey */
    private $surveyRequestHandler;
    /** @var RouterInterface */
    private $router;

    /**
     * @param Survey          $surveyRequestHandler
     * @param RouterInterface $router
     */
    public function __construct(Survey $surveyRequestHandler, RouterInterface $router)
    {
        $this->surveyRequestHandler = $surveyRequestHandler;
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

        if (!$this->surveyRequestHandler->hasSurveyId($request)) {
            return;
        }

        $survey = $this->surveyRequestHandler->fetchSurvey($request);
        if ($survey) {
            $this->surveyRequestHandler->setSurvey($request, $survey);
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
