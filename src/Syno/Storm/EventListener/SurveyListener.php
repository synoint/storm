<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Syno\Storm\Document;
use Syno\Storm\RequestHandler\Response;
use Syno\Storm\RequestHandler\Survey;
use Syno\Storm\Traits\RouteAware;

class SurveyListener implements EventSubscriberInterface
{
    use RouteAware;

    /** @var Survey */
    private $surveyRequestHandler;
    /** @var Response */
    private $responseRequestHandler;
    /** @var RouterInterface */
    private $router;

    /**
     * @param Survey          $surveyRequestHandler
     * @param Response        $responseRequestHandler
     * @param RouterInterface $router
     */
    public function __construct(Survey $surveyRequestHandler, Response $responseRequestHandler, RouterInterface $router)
    {
        $this->surveyRequestHandler   = $surveyRequestHandler;
        $this->responseRequestHandler = $responseRequestHandler;
        $this->router                 = $router;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$event->isMasterRequest() ||
            $this->isApiRoute($request) ||
            !$this->surveyRequestHandler->hasSurveyId($request)) {
            return;
        }

        $surveyId = $this->surveyRequestHandler->getSurveyId($request);
        if (!$surveyId) {
            return;
        }

        $survey = null;
        if ($this->isDebugRoute($request)) {
            $versionId = $this->surveyRequestHandler->getVersionId($request);
            if ($versionId) {
                $survey = $this->surveyRequestHandler->findSavedBySurveyIdAndVersion($surveyId, $versionId);
            }
        }

        if (!$survey) {
            $responseId = $this->responseRequestHandler->getResponseId($request, $surveyId);
            if ($responseId) {
                $surveyResponse = $this->responseRequestHandler->getSavedResponse($surveyId, $responseId);
                if ($surveyResponse) {
                    // it's possible to debug an unpublished version
                    if ($surveyResponse->isDebug()) {
                        $survey = $this->surveyRequestHandler->findSavedBySurveyIdAndVersion(
                            $surveyResponse->getSurveyId(),
                            $surveyResponse->getSurveyVersion()
                        );
                    }
                }
            }
        }

        if (!$survey) {
            $survey = $this->surveyRequestHandler->getPublished($surveyId);
        }

        if ($survey) {
            $survey = $this->setLocale($survey, $request->getLocale(), $survey->getPrimaryLanguageLocale());
            $this->surveyRequestHandler->setSurvey($request, $survey);
            return;
        }

        $event->setResponse(new RedirectResponse($this->router->generate('static.unavailable')));
    }

    /**
     * @param Document\Survey $survey
     * @param string        $currentLocale
     * @param string|null   $fallbackLocale
     *
     * @return Document\Survey
     */
    protected function setLocale(Document\Survey $survey, string $currentLocale, string $fallbackLocale = null)
    {
        $survey->setCurrentLocale($currentLocale);

        if (null !== $fallbackLocale) {
            $survey->setFallbackLocale($fallbackLocale);
        }

        return $survey;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10]
        ];
    }
}
