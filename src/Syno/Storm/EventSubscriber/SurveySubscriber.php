<?php

namespace Syno\Storm\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Syno\Storm\RequestHandler\Survey;
use Syno\Storm\Services\SurveyEventLogger;
use Syno\Storm\Traits\RouteAware;

class SurveySubscriber implements EventSubscriberInterface
{
    use RouteAware;

    private Survey            $surveyHandler;
    private SurveyEventLogger $surveyEventLogger;

    public function __construct(Survey $surveyHandler, SurveyEventLogger $surveyEventLogger)
    {
        $this->surveyHandler     = $surveyHandler;
        $this->surveyEventLogger = $surveyEventLogger;
    }

    /**
     * Extracts survey document by surveyId request attribute
     *
     * @param RequestEvent $event
     */
    public function setSurvey(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->isApiRoute($event->getRequest()) || $this->isCookieCheck($event->getRequest())) {
            return;
        }

        if (!$this->surveyHandler->hasId()) {
            return;
        }

        $survey   = null;
        $surveyId = $this->surveyHandler->getId();

        if ($this->isDebugRoute($event->getRequest())) {
            $versionId = $this->surveyHandler->getVersionId();
            if ($versionId) {
                $survey = $this->surveyHandler->findSaved($surveyId, $versionId);
            }
        }

        if (!$survey) {
            $survey = $this->surveyHandler->getPublished($surveyId);
        }

        if (!$survey) {
            throw new NotFoundHttpException('survey.unavailable');
        }

        $this->surveyHandler->setSurvey($survey);
    }

    public function setLocale(RequestEvent $event)
    {
        if (!$this->surveyHandler->hasSurvey()) {
            return;
        }

        $survey = $this->surveyHandler->getSurvey();
        $survey->setCurrentLocale($event->getRequest()->getLocale());

        if (null !== $survey->getPrimaryLanguageLocale()) {
            $survey->setFallbackLocale($survey->getPrimaryLanguageLocale());
        }

        $this->surveyHandler->setSurvey($survey);
    }

    /**
     * Logs visit to the survey upon entrance
     *
     * @param RequestEvent $event
     */
    public function logVisit(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->isSurveyEntrance($event->getRequest())) {
            return;
        }

        if (!$this->surveyHandler->hasSurvey()) {
            return;
        }

        if ($this->surveyHandler->isLiveRoute($event->getRequest())) {
            $this->surveyEventLogger->log(
                SurveyEventLogger::LIVE_VISIT,
                $this->surveyHandler->getSurvey()
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['setSurvey', 10],
                ['setLocale'],
                ['logVisit']
            ]
        ];
    }
}
