<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Document;
use Syno\Storm\Form\PageType;
use Syno\Storm\Services\ResponseRequest;
use Syno\Storm\Services\SurveySession;


class PageController extends AbstractController
{
    /** @var ResponseRequest */
    private $responseRequestService;

    /** @var SurveySession */
    private $surveySessionService;

    /**
     * @param ResponseRequest $responseRequestService
     * @param SurveySession   $surveySessionService
     */
    public function __construct(ResponseRequest $responseRequestService, SurveySession $surveySessionService)
    {
        $this->responseRequestService = $responseRequestService;
        $this->surveySessionService   = $surveySessionService;
    }

    /**
     * @param Document\Survey   $survey
     * @param Document\Page     $page
     * @param Document\Response $response
     * @param Request           $request
     *
     * @Route(
     *     "%app.route_prefix%/p/{surveyId}/{pageId}",
     *     name="page.index",
     *     requirements={"surveyId"="\d+", "pageId"="\d+"},
     *     methods={"GET","POST"}
     * )
     *
     * @return Response
     */
    public function index(
        Document\Survey $survey,
        Document\Page $page,
        Document\Response $response,
        Request $request
    ): Response
    {
        $form = $this->createForm(PageType::class, null, [
            'questions' => $page->getQuestions()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Document\Question $question */
            foreach ($page->getQuestions() as $question) {
                $answers = $this->responseRequestService->extractAnswers($question, $form->getData());
                $response->addAnswer(
                    new Document\ResponseAnswer($question->getQuestionId(), $answers)
                );
            }
            $this->responseRequestService->saveResponse($response);

            $nextPage = $survey->getNextPage($page->getPageId());
            if (null === $nextPage) {

                $this->surveySessionService->grantComplete($survey->getSurveyId());

                return $this->redirectToRoute('survey.complete', [
                    'surveyId' => $survey->getSurveyId()
                ]);
            }

            return $this->redirectToRoute('page.index', [
                'surveyId' => $survey->getSurveyId(),
                'pageId'   => $nextPage->getPageId()
            ]);
        }

        return $this->render($survey->getConfig()->theme . '/page/display.twig', [
            'survey'             => $survey,
            'page'               => $page,
            'response'           => $response,
            'form'               => $form->createView(),
            'backButtonDisabled' => $survey->isFirstPage($page->getPageId())
        ]);
    }

    /**
     * @Route("%app.route_prefix%/p/unavailable", name="page.unavailable")
     *
     * @return Response|RedirectResponse
     */
    public function unavailable()
    {
        return $this->render(Document\Config::DEFAULT_THEME . '/page/unavailable.twig');
    }
}
