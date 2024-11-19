<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Api\Controller\TokenAuthenticatedController;
use Syno\Storm\Api\v1\Form;
use Syno\Storm\Api\v1\Http\ApiResponse;
use Syno\Storm\Services\Page;
use Syno\Storm\Services\Randomization;
use Syno\Storm\Services\Survey;
use Syno\Storm\Services\SurveyPath;
use Syno\Storm\Traits\FormAware;
use Syno\Storm\Traits\JsonRequestAware;

/**
 * @Route("/api/v1/survey")
 */
class RandomizationController extends AbstractController implements TokenAuthenticatedController
{
    use FormAware;
    use JsonRequestAware;

    private Survey        $surveyService;
    private SurveyPath    $surveyPathService;
    private Page          $pageService;
    private Randomization $randomizationService;

    public function __construct(
        Survey        $surveyService,
        SurveyPath    $surveyPathService,
        Page          $pageService,
        Randomization $randomizationService
    )
    {
        $this->surveyService        = $surveyService;
        $this->surveyPathService    = $surveyPathService;
        $this->pageService          = $pageService;
        $this->randomizationService = $randomizationService;
    }

    /**
     * @Route(
     *     "/{surveyId}/versions/{version}/randomization",
     *     name="storm_api.v1.survey.randomization.save",
     *     requirements={"surveyId"="\d+", "versionId"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function save(Request $request, int $surveyId, int $version): JsonResponse
    {
        $data   = $this->getJson($request);
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);

        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }

        $survey->getRandomization()->clear();

        $form = $this->createForm(Form\SurveyRandomizationType::class, $survey);
        $form->submit(['randomization' => $data]);

        if ($form->isValid()) {
            $this->surveyPathService->deleteBySurveyId($survey->getSurveyId());

            $this->surveyService->save($survey);

            $survey->setPages($this->pageService->findAllBySurvey($survey));

            $randomizedCombinations = $this->randomizationService->getRandomizedPaths($survey);

            $count = $this->surveyPathService->save($survey, $randomizedCombinations);

            return $this->json('Survey randomization is added. Count: ' . $count, 201);
        }

        return new ApiResponse('Survey randomization creation failed!', null, $this->getFormErrors($form), 400);
    }
}
