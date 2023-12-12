<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Api\Controller\TokenAuthenticatedController;
use Syno\Storm\Services\Page;
use Syno\Storm\Services\Survey;
use Syno\Storm\Services\SurveyPath;

/**
 * @Route("/api/v1/survey-path")
 */
class SurveyPathController extends AbstractController implements TokenAuthenticatedController
{
    private Page       $pageService;
    private Survey     $surveyService;
    private SurveyPath $surveyPathService;

    public function __construct(Page $pageService, Survey $surveyService, SurveyPath $surveyPathService)
    {
        $this->pageService       = $pageService;
        $this->surveyService     = $surveyService;
        $this->surveyPathService = $surveyPathService;
    }


    /**
     * @Route(
     *     "/{surveyId}/{version}",
     *     name="storm_api.v1.survey_path.retrieve",
     *     requirements={"id"="\d+", "version"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function retrieve(int $surveyId, int $version): JsonResponse
    {
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);
        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }

        $surveyPath = $this->surveyPathService->getRandomSurveyPath($survey);

        return $this->json(
            [
                'id'    => $surveyPath->getSurveyPathId(),
                'pages' => $surveyPath->getPages(),
                'first' => $surveyPath->getFirstPageId(),
                'next'  => $surveyPath->getNextPageId($surveyPath->getFirstPageId()),
                'last'  => $surveyPath->getLastPageId(),
            ]
        );
    }
}
