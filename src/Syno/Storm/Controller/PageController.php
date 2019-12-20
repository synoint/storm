<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Document;
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
     * @param int $surveyId
     * @param int $pageId
     *
     * @Route(
     *     "%app.route_prefix%/s/{surveyId}/{pageId}",
     *     name="page.display",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function display(int $surveyId, int $pageId): Response
    {
        $survey = $this->getSurvey($surveyId);
        $page = $survey->getPage($pageId);

        if (!$page) {
            throw $this->createNotFoundException('This page is no longer available');
        }

        return $this->render($survey->getConfig()->theme . '/page/display.twig', [
            'page' => $page
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
