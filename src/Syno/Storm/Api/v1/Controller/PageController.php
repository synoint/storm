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
use Syno\Storm\Services\Page;
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
    private Page $pageService;

    public function __construct(Survey $surveyService, Page $pageService)
    {
        $this->surveyService = $surveyService;
        $this->pageService   = $pageService;
    }

    /**
     * @Route(
     *     "/{surveyId}/versions/{version}/pages",
     *     name="storm_api.v1.page.create",
     *     requirements={"surveyId"="\d+", "versionId"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function create(Request $request, int $surveyId, int $version): JsonResponse
    {
        $data   = $this->getJson($request);
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);

        if(!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }

        $page = new Document\Page();

        $form = $this->createForm(Form\PageType::class, $page);
        $form->submit($data);

        if ($form->isValid()) {
            $this->pageService->save($page);

            return $this->json($page->getPageId());
        }

        return new ApiResponse('Page creation failed!', null, $this->getFormErrors($form), 400);
    }

    /**
     * @Route(
     *     "/{surveyId}/versions/{version}/pages/copy",
     *     name="storm_api.v1.page.copy",
     *     requirements={"surveyId"="\d+", "versionId"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function copy(int $surveyId, int $version): JsonResponse
    {
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);

        if(!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }

        $surveyPages = $survey->getPages();

        /** @var Document\PageInterface $surveyPage */
        foreach ($surveyPages as $surveyPage) {
            if (!$this->pageService->pageExists($surveyPage->getPageId(), $survey->getSurveyId(), $survey->getVersion())) {
                $newPage = new Document\Page();
                $newPage->setId($surveyPage->getId());
                $newPage->setPageId($surveyPage->getPageId());
                $newPage->setSurveyId($survey->getSurveyId());
                $newPage->setVersion($survey->getVersion());
                $newPage->setCode($surveyPage->getCode());
                $newPage->setSortOrder($surveyPage->getSortOrder());
                $newPage->setTranslations($surveyPage->getTranslations());
                $newPage->setJavascript($surveyPage->getJavascript());
                $newPage->setQuestions($surveyPage->getQuestions());

                $this->pageService->save($newPage);
            }
        }

        return $this->json('ok');
    }
}
