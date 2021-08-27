<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Api\Controller\TokenAuthenticatedController;
use Syno\Storm\Services\SurveyEvent;
use Syno\Storm\Services\ResponseEvent;

/**
 * @Route("/api/v1")
 */
class EventController extends AbstractController implements TokenAuthenticatedController
{
    private ResponseEvent $responseEvent;
    private SurveyEvent   $surveyEvent;

    public function __construct(ResponseEvent $responseEvent, SurveyEvent $surveyEvent)
    {
        $this->responseEvent = $responseEvent;
        $this->surveyEvent   = $surveyEvent;
    }

    /**
     * @Route(
     *     "/survey-events",
     *     name="storm_api.v1.event.survey_events",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function surveyEvents(Request $request): JsonResponse
    {
        return $this->json(
            $this->surveyEvent->getAll(
                $request->query->getAlnum('offset'),
                $request->query->getInt('limit', 1000)
            )
        );
    }

    /**
     * @Route(
     *     "/response-events",
     *     name="storm_api.v1.event.response_events",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function responseEvents(Request $request): JsonResponse
    {
        return $this->json(
            $this->responseEvent->getAll(
                $request->query->getAlnum('offset'),
                $request->query->getInt('limit', 1000)
            )
        );
    }
}
