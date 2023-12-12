<?php

namespace Syno\Storm\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Syno\Storm\Event\ResponseComplete;
use Syno\Storm\Message\ProfilingSurvey;
use Syno\Storm\Message\SurveyNotification;
use Syno\Storm\Services;
use Syno\Storm\Document;

class ResponseCompleteSubscriber implements EventSubscriberInterface
{
    private Services\ResponseDataLayer $responseDataLayerService;
    private Services\SurveyConfig      $surveyConfigService;
    private Services\ResponseEvent     $responseEventService;
    private Services\Response          $responseService;
    private SerializerInterface        $serializer;
    private MessageBusInterface        $bus;

    public function __construct(
        Services\ResponseDataLayer $responseDataLayerService,
        Services\SurveyConfig      $surveyConfigService,
        Services\ResponseEvent     $responseEventService,
        Services\Response          $responseService,
        SerializerInterface        $serializer,
        MessageBusInterface        $bus
    ) {
        $this->responseDataLayerService = $responseDataLayerService;
        $this->surveyConfigService      = $surveyConfigService;
        $this->responseEventService     = $responseEventService;
        $this->responseService          = $responseService;
        $this->serializer               = $serializer;
        $this->bus                      = $bus;
    }

    public function onResponseComplete(ResponseComplete $event)
    {
        $survey   = $event->getSurvey();
        $response = $event->getResponse();
        $answers  = $this->responseDataLayerService->getData($survey, $response);

        if ($this->surveyConfigService->findBySurveyIdAndKey(
            $survey->getSurveyId(),
            Document\SurveyConfig::EMAIL_NOTIFICATION)) {
            $response->setCompletedAt($this->responseEventService->getResponseCompletionTime($response->getResponseId()));

            $data = $this->responseService->toArrayWithAnswerLabels(
                $response,
                $answers['answers'],
                $this->responseEventService->getEventsByResponseId($response->getResponseId())
            );

            $this->bus->dispatch(
                new SurveyNotification(
                    $survey->getSurveyId(),
                    Document\SurveyConfig::EMAIL_NOTIFICATION,
                    $this->serializer->serialize($data, 'json')
                )
            );
        }

        if ($survey->getCompleteCallbackUrl()) { // Todo refactor in notification queue
            $this->bus->dispatch(
                new ProfilingSurvey(
                    $survey->getCompleteCallbackUrl(),
                    $response
                )
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResponseComplete::class => 'onResponseComplete',
        ];
    }
}
