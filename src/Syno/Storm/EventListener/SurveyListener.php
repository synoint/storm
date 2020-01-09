<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Services\Survey;

class SurveyListener implements EventSubscriberInterface
{
    CONST ATTR = 'survey';

    /** @var Survey */
    private $surveyService;
    /** @var RouterInterface */
    private $router;

    /**
     * @param Survey          $surveyService
     * @param RouterInterface $router
     */
    public function __construct(Survey $surveyService, RouterInterface $router)
    {
        $this->surveyService = $surveyService;
        $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if (false !== strpos($request->attributes->get('_route'), 'storm_api')) {
            return;
        }

        if (!$request->attributes->has('surveyId')) {
            return;
        }

        $surveyId = $request->attributes->getInt('surveyId');
        if ($surveyId) {
            if ('survey.debug' === $request->attributes->get('_route')) {
                $versionId = $request->attributes->getInt('versionId', $this->surveyService->findLatestVersion($surveyId));
                $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $versionId);
            } else {
                $survey = $this->surveyService->getPublished($surveyId);
            }

            if ($survey) {
                $request->attributes->set(self::ATTR, $survey);
                return;
            }
        }

        $event->setResponse(new RedirectResponse($this->router->generate('survey.unavailable')));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 8],
        ];
    }
}
