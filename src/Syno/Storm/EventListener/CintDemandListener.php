<?php

namespace Syno\Storm\EventListener;

use Syno\Storm\Traits\RouteAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Syno\Storm\Services\Provider;
use Syno\Storm\RequestHandler;

class CintDemandListener implements EventSubscriberInterface
{
    use RouteAware;

    const SOURCE = 2;

    /** @var RequestHandler\Survey */
    private $surveyRequestHandler;

    /** @var RequestHandler\Response */
    private $responseRequestHandler;

    /** @var Provider\CintDemand */
    private $cintDemandService;

    /**
     * @param RequestHandler\Survey   $surveyRequestHandler
     * @param RequestHandler\Response $responseRequestHandler
     * @param Provider\CintDemand     $cintDemandService
     */
    public function __construct(
        RequestHandler\Survey $surveyRequestHandler,
        RequestHandler\Response $responseRequestHandler,
        Provider\CintDemand $cintDemandService
    )
    {
        $this->surveyRequestHandler   = $surveyRequestHandler;
        $this->responseRequestHandler = $responseRequestHandler;
        $this->cintDemandService      = $cintDemandService;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if(!$this->surveyRequestHandler->hasSurvey($request)){
            return;
        }

        $survey = $this->surveyRequestHandler->getSurvey($request);
        $responseId = $this->responseRequestHandler->getResponseId($request, $survey->getSurveyId());

        if (empty($responseId)) {
            return;
        }

        $surveyResponse = $this->responseRequestHandler->getSavedResponse($survey->getSurveyId(), $responseId);

        if($surveyResponse->getSource() != self::SOURCE) {
            return;
        }

        $outcomeStatus = null;

        if ($surveyResponse->isCompleted()) {
            $outcomeStatus = $this->cintDemandService::STATUS_COMPLETE;
        }

        if ($surveyResponse->isScreenedOut()) {
            $outcomeStatus = $this->cintDemandService::STATUS_SCREENOUT;
        }

        if ($surveyResponse->isQualityScreenedOut()) {
            $outcomeStatus = $this->cintDemandService::STATUS_QUALITY_TERMINATE;
        }

        if ($surveyResponse->isQuotaFull()) {
            $outcomeStatus = $this->cintDemandService::STATUS_QUOTA_FULL;
        }

        if($outcomeStatus) {
            $this->cintDemandService->submitStatus($survey, $surveyResponse, $outcomeStatus);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE   => ['onKernelResponse'],
        ];
    }
}