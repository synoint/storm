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
    private const LIMIT = 1000;

    use FormAware;
    use JsonRequestAware;

    private Response            $responseService;
    private ResponseEvent       $responseEventService;
    private ResponseEventLogger $responseEventLogger;

    public function __construct(
        Response            $responseService,
        ResponseEvent       $responseEventService,
        ResponseEventLogger $responseEventLogger)
    {
        $this->responseService      = $responseService;
        $this->responseEventService = $responseEventService;
        $this->responseEventLogger  = $responseEventLogger;
    }

    /**
     * @Route(
     *     "/{surveyId}/responses/total",
     *     name="storm_api.v1.response.total",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function total(int $surveyId): JsonResponse
    {
        return $this->json($this->responseService->count($surveyId));
    }

    /**
     * @Route(
     *     "/{surveyId}/responses/query",
     *     name="storm_api.v1.response.query",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function query(int $surveyId, Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', self::LIMIT);
        $limit = min($limit, self::LIMIT);
        $limit = max($limit, 1);

        $offset = $request->query->getInt('offset');
        $offset = max($offset, 0);

        $responses = $this->responseService->getAllBySurveyId($surveyId, $limit, $offset);
        $total     = $this->responseService->count($surveyId);

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
                'offset'    => $offset,
                'limit'     => $limit,
                'total'     => $total,
                'responses' => $responses
            ]
        );
    }

    /**
     * @Route(
     *     "/{surveyId}/responses",
     *     name="storm_api.v1.response.all",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function all(int $surveyId, Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 1000000);
        $limit = max($limit, 1);

        $responses = $this->responseService->getAllBySurveyId($surveyId, $limit, 0, []);
        $total     = count($responses);

        if ($total) {
            $completesMap = $this->responseEventService->getResponseCompletionTimeMap($surveyId);
            /** @var Document\Response $response */
            foreach ($responses as $response) {
                if ($response->isCompleted()) {
                    $response->setCompletedAt($completesMap[$response->getResponseId()] ?? 0);
                }
            }
        }

        return $this->json(['responses' => $responses]);
    }

    /**
     * @Route(
     *     "/{surveyId}/response/complete",
     *     name="storm_api.v1.response.complete",
     *     requirements={"surveyId"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function complete(int $surveyId, Request $request): JsonResponse
    {
        $responseIds = json_decode($request->getContent());

        if ($responseIds) {
            foreach ($responseIds as $responseId) {
                $response = $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);

                if ($response) {
                    $response->setQualityScreenedOut(false);
                    $response->setQuotaFull(false);
                    $response->setScreenedOut(false);
                    $response->setCompleted(true);

                    $this->responseService->save($response);

                    $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_COMPLETE, $response);
                }
            }

            return $this->json(['message' => 'Updated!']);
        }

        return $this->json(['message' => 'Response ids is missing.'], 400);
    }

    /**
     * @Route(
     *     "/{surveyId}/response/screenout",
     *     name="storm_api.v1.response.screenout",
     *     requirements={"surveyId"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function screenout(int $surveyId, Request $request): JsonResponse
    {
        $responseIds = json_decode($request->getContent());

        if ($responseIds) {
            foreach ($responseIds as $responseId) {
                $response = $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);

                if ($response) {

                    $response->setQualityScreenedOut(false);
                    $response->setQuotaFull(false);
                    $response->setCompleted(false);

                    $response->setScreenedOut(true);

                    $this->responseService->save($response);

                    $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_SCREENOUT, $response);
                }
            }

            return $this->json(['message' => 'Updated!']);
        }

        return $this->json(['message' => 'Response ids is missing.'], 400);
    }

    /**
     * @Route(
     *     "/{surveyId}/response/quality-screenout",
     *     name="storm_api.v1.response.quality_screenout",
     *     requirements={"surveyId"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function qualityScreenout(int $surveyId, Request $request): JsonResponse
    {
        $responseIds = json_decode($request->getContent());

        if ($responseIds) {
            foreach ($responseIds as $responseId) {
                $response = $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);

                if ($response) {

                    $response->setCompleted(false);
                    $response->setQuotaFull(false);
                    $response->setScreenedOut(false);

                    $response->setQualityScreenedOut(true);

                    $this->responseService->save($response);

                    $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_QUALITY_SCREENOUT, $response);
                }
            }

            return $this->json(['message' => 'Updated!']);
        }

        return $this->json(['message' => 'Response ids is missing.'], 400);
    }

    /**
     * @Route(
     *     "/{surveyId}/response/quota-full",
     *     name="storm_api.v1.response.quota_full",
     *     requirements={"surveyId"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function quotaFull(int $surveyId, Request $request): JsonResponse
    {
        $responseIds = json_decode($request->getContent());

        if ($responseIds) {
            foreach ($responseIds as $responseId) {

                $response = $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);

                if ($response) {

                    $response->setQualityScreenedOut(false);
                    $response->setCompleted(false);
                    $response->setScreenedOut(false);

                    $response->setQuotaFull(true);

                    $this->responseService->save($response);

                    $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_QUOTA_FULL, $response);
                }
            }

            return $this->json(['message' => 'Updated!']);
        }

        return $this->json(['message' => 'Response ids is missing.'], 400);
    }

    /**
     * @Route(
     *     "/{surveyId}/response/remove",
     *     name="storm_api.v1.response.remove",
     *     requirements={"surveyId"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function remove(int $surveyId, Request $request): JsonResponse
    {
        $responseIds = json_decode($request->getContent());

        if ($responseIds) {
            foreach ($responseIds as $responseId) {

                $response = $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);

                if ($response) {

                    $response->setDeletedAt(new \DateTime());

                    $this->responseService->save($response);

                    $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_REMOVE, $response);
                }
            }

            return $this->json(['message' => 'Removed!']);
        }

        return $this->json(['message' => 'Response ids is missing.'], 400);
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
                $completedAt = $this->responseEventService->getResponseCompletionTime($responseId);
                if ($completedAt) {
                    $response->setCompletedAt($completedAt);
                }
            }
            $response->setEvents($this->responseEventService->getEventsByResponseId($responseId));

            return $this->json($response);
        }

        return $this->json('Response data not found', 404);
    }

    /**
     * @Route(
     *     "/{surveyId}/response/restore",
     *     name="storm_api.v1.response.restore",
     *     requirements={"surveyId"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function restore(int $surveyId, Request $request): JsonResponse
    {
        $responseIds = json_decode($request->getContent());

        if ($responseIds) {
            foreach ($responseIds as $responseId) {

                $response = $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);

                if ($response) {

                    $response->setDeletedAt(null);

                    $this->responseService->save($response);

                    $this->responseEventLogger->log(ResponseEventLogger::RESPONSE_RESTORE, $response);
                }
            }

            return $this->json(['message' => 'Restored!']);
        }

        return $this->json(['message' => 'Response ids is missing.'], 400);
    }

    /**
     * @Route(
     *     "/{surveyId}/responses",
     *     name="storm_api.v1.response.delete",
     *     requirements={"surveyId"="\d+"},
     *     methods={"DELETE"}
     * )
     */
    public function delete(int $surveyId, Request $request): JsonResponse
    {
        $deleted = 0;
        $responseIds = json_decode($request->getContent());

        if ($responseIds) {
            foreach ($responseIds as $responseId) {
                $response = $this->responseService->findBySurveyIdAndResponseId($surveyId, $responseId);
                if ($response) {
                    $this->responseEventService->deleteEvents($response->getResponseId());
                    $this->responseService->delete($response);
                    $deleted++;
                }
            }

            return $this->json(['message' => $deleted]);
        }

        return $this->json(['message' => 'Response ids is missing.'], 400);
    }
}
