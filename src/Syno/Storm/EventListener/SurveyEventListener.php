<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Syno\Storm\RequestHandler\Survey;
use Syno\Storm\Services\SurveyEventLogger;
use Syno\Storm\Traits\RouteAware;

class SurveyEventListener implements EventSubscriberInterface
{
    use RouteAware;

    /** @var Survey */
    private $surveyRequestHandler;
    /** @var SurveyEventLogger */
    private $surveyEventLogger;

    /**
     * @param Survey            $surveyRequestHandler
     * @param SurveyEventLogger $surveyEventLogger
     */
    public function __construct(Survey $surveyRequestHandler, SurveyEventLogger $surveyEventLogger)
    {
        $this->surveyRequestHandler = $surveyRequestHandler;
        $this->surveyEventLogger    = $surveyEventLogger;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();

        if ($event->isMasterRequest() && $this->isSurveyEntrance($request->attributes->get('_route'))) {
            $survey = $this->surveyRequestHandler->getSurvey($request);
            if ($survey) {
                $this->surveyEventLogger->log(SurveyEventLogger::VISIT, $survey);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['onKernelRequest', 2]
        ];
    }
}
