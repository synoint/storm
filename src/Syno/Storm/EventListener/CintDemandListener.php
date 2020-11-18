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

        if ($surveyResponse->isCompleted()) {
            $this->cintDemandService->submitComplete($surveyResponse, $survey);
        }

        if ($surveyResponse->isScreenedOut()) {
            $this->cintDemandService->submitScreenOut($surveyResponse, $survey);
        }

        if ($surveyResponse->isQualityScreenedOut()) {
            $this->cintDemandService->submitQualityScreenOut($surveyResponse, $survey);
        }

        if ($surveyResponse->isQuotaFull()) {
            $this->cintDemandService->submitQuotaFull($surveyResponse, $survey);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE   => ['onKernelResponse'],
        ];
    }
}