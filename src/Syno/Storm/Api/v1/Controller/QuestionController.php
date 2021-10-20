<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function answersResultCount(int $questionId, Request $request): JsonResponse
    {
        $data   = [];
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

        $responses = $this->responseService->getAllByQuestionId($questionId, $params);

        foreach ($responses as $response) {
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

        return $this->json($data);
    }
}
