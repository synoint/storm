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
     *     name="page.index",
     *     requirements={"surveyId"="\d+", "pageId"="\d+"},
     *     methods={"GET","POST"}
     * )
     *
     * @return Response|RedirectResponse
     */
    public function index(int $surveyId, int $pageId, Request $request): Response
    {
        $survey = $this->surveyService->getPublished($surveyId);
        if (!$survey) {
            return $this->redirectToRoute('survey.unavailable');
        }

        $page = $survey->getPage($pageId);
        if (!$page) {
            return $this->redirectToRoute('page.unavailable');
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

            return $this->redirectToRoute('page.index', [
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
     * @Route("%app.route_prefix%/page/unavailable", name="page.unavailable")
     *
     * @return Response|RedirectResponse
     */
    public function unavailable()
    {
        return $this->render(Document\Config::DEFAULT_THEME . '/page/unavailable.twig');
    }
}
