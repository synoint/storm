<?php

namespace Syno\Storm\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Syno\Storm\Event\SurveyCompleted;
use Syno\Storm\RequestHandler\Survey;
use Syno\Storm\Services\SurveyStats;
use Syno\Storm\Traits\RouteAware;

class SurveyStatsListener implements EventSubscriberInterface
{
    use RouteAware;

    /** @var Survey */
    private $surveyRequestHandler;
    /** @var SurveyStats */
    private $surveyStatsService;

    /**
     * @param Survey      $surveyRequestHandler
     * @param SurveyStats $surveyStatsService
     */
    public function __construct(Survey $surveyRequestHandler, SurveyStats $surveyStatsService)
    {
        $this->surveyRequestHandler = $surveyRequestHandler;
        $this->surveyStatsService   = $surveyStatsService;
    }


    public function onKernelRequest(RequestEvent $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();

        if ($event->isMasterRequest() && $this->isSurveyEntrance($request->attributes->get('_route'))) {
            $survey = $this->surveyRequestHandler->getSurvey($request);
            if ($survey) {
                $this->surveyStatsService->incrementVisits($survey->getSurveyId(), $survey->getVersion());
            }
        }
    }

    /**
     * @param SurveyCompleted $event
     */
    public function onSurveyCompleted(SurveyCompleted $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();

        $survey = $this->surveyRequestHandler->getSurvey($request);
        if ($survey) {
            $this->surveyStatsService->incrementCompletes($survey->getSurveyId(), $survey->getVersion());
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['onKernelRequest', 2],
            SurveyCompleted::class => ['onSurveyCompleted']
        ];
    }
}
