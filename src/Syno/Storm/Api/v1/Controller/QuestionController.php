<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Api\Controller\TokenAuthenticatedController;
use Syno\Storm\Services\Response;

/**
 * @Route("/api/v1/questions")
 */
class QuestionController extends AbstractController implements TokenAuthenticatedController
{
    private Response $responseService;

    public function __construct(Response $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * @Route(
     *     "/{questionId}/answers-result-count",
     *     name="storm_api.v1.question.answersResultCount",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function answersResultCount(int $questionId): JsonResponse
    {
        $data      = [];
        $responses = $this->responseService->getAllByQuestionId($questionId);
        foreach ($responses as $response) {
            if (!$response->isLowQuality()) {
                foreach ($response->getAnswers() as $responseAnswer) {
                    if ($responseAnswer->getQuestionId() == $questionId) {
                        foreach ($responseAnswer->getAnswers() as $answer) {
                            if (!isset($data[$answer->getAnswerId()])) {
                                $data[$answer->getAnswerId()] = 0;
                            }
                            $data[$answer->getAnswerId()] += 1;
                        }
                    }
                }
            }
        }

        return $this->json($data);
    }
}
