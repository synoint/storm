<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Api\Controller\TokenAuthenticatedController;
use Syno\Storm\Api\v1\Form;
use Syno\Storm\Api\v1\Http\ApiResponse;
use Syno\Storm\Document;
use Syno\Storm\Services\Survey;
use Syno\Storm\Traits\FormAware;
use Syno\Storm\Traits\JsonRequestAware;

/**
 * @Route("/api/v1/survey")
 */
class PageController extends AbstractController implements TokenAuthenticatedController
{
    use FormAware;
    use JsonRequestAware;

    private Survey $surveyService;

    public function __construct(Survey $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    /**
     * @Route(
     *     "/{surveyId}/versions/{versionId}/pages/create",
     *     name="storm_api.v1.survey.page.create",
     *     requirements={"surveyId"="\d+", "versionId"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function create(Request $request, int $surveyId, int $versionId): JsonResponse
    {
        $data   = $this->getJson($request);
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $versionId);

        $page = new Document\Page();

        $form = $this->createForm(Form\PageType::class, $page);
        $form->submit($data);

        if ($form->isValid()) {
            $survey->getPages()->add($page);
            $this->surveyService->save($survey);

            return $this->json($survey->getId());
        }

        return new ApiResponse('Survey creation failed!', null, $this->getFormErrors($form), 400);
    }

    /**
     * @Route(
     *     "/{surveyId}/versions/{versionId}/pages/delete",
     *     name="storm_api.v1.survey.page.delete",
     *     requirements={"surveyId"="\d+", "versionId"="\d+"},
     *     methods={"DELETE"}
     * )
     */
    public function delete(int $surveyId, int $versionId): JsonResponse
    {
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $versionId);

        $survey->getPages()->clear();

        $this->surveyService->save($survey);

        return $this->json('Pages deleted');
    }
}
