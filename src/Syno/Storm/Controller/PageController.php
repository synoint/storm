<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Document;
use Syno\Storm\Form\PageType;
use Syno\Storm\Services\Survey;
use Syno\Storm\Services\SurveySession;


class PageController extends AbstractController
{
    /** @var Survey */
    private $surveyService;

    /** @var SurveySession */
    private $surveySessionService;

    /**
     * @param Survey        $surveyService
     * @param SurveySession $surveySessionService
     */
    public function __construct(Survey $surveyService, SurveySession $surveySessionService)
    {
        $this->surveyService        = $surveyService;
        $this->surveySessionService = $surveySessionService;
    }


    /**
     * @param int     $surveyId
     * @param int     $pageId
     * @param Request $request
     *
     * @Route(
     *     "%app.route_prefix%/p/{surveyId}/{pageId}",
     *     name="page.display",
     *     requirements={"surveyId"="\d+", "pageId"="\d+"},
     *     methods={"GET","POST"}
     * )
     *
     * @return Response|RedirectResponse
     */
    public function display(int $surveyId, int $pageId, Request $request): Response
    {
        $survey = $this->getSurvey($surveyId);
        $page = $survey->getPage($pageId);

        if (!$page) {
            throw $this->createNotFoundException('This page is no longer available');
        }

        $form = $this->createForm(PageType::class, null, [
            'page' => $page
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nextPage = $survey->getNextPage($pageId);
            if (null === $nextPage) {
                return $this->redirectToRoute('survey.complete', [
                    'surveyId' => $surveyId
                ]);
            }

            return $this->redirectToRoute('page.display', [
                'surveyId' => $surveyId,
                'pageId'   => $nextPage->getPageId()
            ]);
        }

        return $this->render($survey->getConfig()->theme . '/page/display.twig', [
            'page'               => $page,
            'form'               => $form->createView(),
            'backButtonDisabled' => $survey->isFirstPage($pageId)
        ]);
    }

    /**
     * @param int $surveyId
     *
     * @return Document\Survey
     */
    protected function getSurvey(int $surveyId)
    {
        $survey = $this->surveyService->getPublished($surveyId);
        if (!$survey) {
            throw $this->createNotFoundException('This survey is no longer available');
        }

        return $survey;
    }
}
