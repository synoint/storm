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
use Syno\Storm\Traits\FormAware;
use Syno\Storm\Traits\JsonRequestAware;

/**
 * @Route("/api/v1/survey")
 */
class ResponseController extends AbstractController implements TokenAuthenticatedController
{
    use FormAware;
    use JsonRequestAware;

    private Response          $responseService;
    private ResponseEvent     $responseEventService;

    public function __construct(
        Response $responseService,
        ResponseEvent $responseEventService
    ) {
        $this->responseService          = $responseService;
        $this->responseEventService     = $responseEventService;
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
        $total = $this->responseService->count($surveyId);
        $limit = $request->query->getInt('limit', 1000000);
        $limit = max($limit, 1);

        $responses = [];
        if ($total) {
            $responses = $this->responseService->getAllBySurveyId($surveyId, $limit);
            if ($responses) {
                $completesMap = $this->responseEventService->getResponseCompletionTimeMap($surveyId);
                /** @var Document\Response $response */
                foreach ($responses as $response) {
                    if ($response->isCompleted()) {
                        $response->setCompletedAt($completesMap[$response->getResponseId()] ?? 0);
                    }
                }
            }
        }

        return $this->json(
            [
                'responses' => $responses,
                'limit'     => $limit,
                'total'     => $total
            ]
        );
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
        return $this->json('Response not found', 404);
    }

}
