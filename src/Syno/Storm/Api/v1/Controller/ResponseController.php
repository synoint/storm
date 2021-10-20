<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Api\Controller\TokenAuthenticatedController;
use Syno\Storm\Document;
use Syno\Storm\Services\Response;
use Syno\Storm\Services\ResponseEvent;
use Syno\Storm\Services\ResponseEventLogger;
use Syno\Storm\Traits\FormAware;
use Syno\Storm\Traits\JsonRequestAware;

/**
 * @Route("/api/v1/survey")
 */
class ResponseController extends AbstractController implements TokenAuthenticatedController
{
    use FormAware;
    use JsonRequestAware;

    private Response            $responseService;
    private ResponseEvent       $responseEventService;
    private ResponseEventLogger $responseEventLogger;

    /**
     * @param Response            $responseService
     * @param ResponseEvent       $responseEventService
     * @param ResponseEventLogger $responseEventLogger
     */
    public function __construct(
        Response $responseService,
        ResponseEvent $responseEventService,
        ResponseEventLogger $responseEventLogger
    ) {
        $this->responseService      = $responseService;
        $this->responseEventService = $responseEventService;
        $this->responseEventLogger  = $responseEventLogger;
    }

    /**
     * @Route(
     *     "/{surveyId}/responses",
     *     name="storm_api.v1.responses.all",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function all(int $surveyId, Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 1000000);

        $params = [];
        if ($request->query->get('mode')) {
            $params['mode'] = $request->query->get('mode');
        }

        if ($request->query->get('status')) {
            $isCompleted = false;
            if ('completed' === $request->query->get('status')) {
                $isCompleted = true;
            }
            $params['completed'] = $isCompleted;
        }

        $responses = $this->responseService->getAllBySurveyId($surveyId, $limit, 0, $params);
        $total     = count($responses);
        $limit     = max($limit, 1);

        if ($total) {
            $completesMap = $this->responseEventService->getResponseCompletionTimeMap($surveyId);
            /** @var Document\Response $response */
            foreach ($responses as $response) {
                if ($response->isCompleted()) {
                    $response->setCompletedAt($completesMap[$response->getResponseId()] ?? 0);
                }
            }
        }

        return $this->json(
            [
                'responses' => $responses,
                'limit' => $limit,
                'total' => $total
            ]
        );
    }

    /**
     * @Route(
     *     "/{surveyId}/response/{responseId}/quality-status/{status}",
     *     name="storm_api.v1.response.quality_status",
     *     requirements={"surveyId"="\d+", "responseId"=".+", "sttus"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function qualityStatus(int $surveyId, string $responseId, int $status): JsonResponse
    {
        $response = $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);

        if ($response) {
            if ($response->isCompleted()) {
                $response->setQualityScreenedOut($status);
                $this->responseService->save($response);

                $event = ($status) ? ResponseEventLogger::QUALITY_SCREENOUT : ResponseEventLogger::QUALITY_SCREENOUT_CLEARED;
                $this->responseEventLogger->log($event, $response);
            }

            return $this->json($response);
        }

        return $this->json(['message' => 'Response data is not found']);
    }

    /**
     * @Route(
     *     "/{surveyId}/responses/{responseId}",
     *     name="storm_api.v1.response.details",
     *     requirements={"surveyId"="\d+", "responseId"=".+"},
     *     methods={"GET"}
     * )
     */
    public function details(int $surveyId, string $responseId): JsonResponse
    {
        $response = $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);

        if ($response) {
            if ($response->isCompleted()) {
                $response->setCompletedAt($this->responseEventService->getResponseCompletionTime($responseId));
            }
            $response->setEvents($this->responseEventService->getEventsByResponseId($responseId));

            return $this->json($response);
        }

        return $this->json('Response data not found', 404);
    }
}
