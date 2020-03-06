<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Document;
use Syno\Storm\Form\PageType;
use Syno\Storm\RequestHandler;
use Syno\Storm\Services\ResponseEventLogger;
use Syno\Storm\Services\SurveySession;
use Syno\Storm\Services;
use JWadhams;

class PageController extends AbstractController
{
    /** @var RequestHandler\Response */
    private $responseRequestHandler;

    /** @var ResponseEventLogger */
    private $responseEventLogger;

    /** @var SurveySession */
    private $surveySessionService;

    /** @var Services\Response */
    private $responseService;

    /** @var Services\Condition */
    private $conditionService;

    /** @var Services\Page */
    private $pageService;


    /**
     * @param RequestHandler\Response $responseRequestHandler
     * @param ResponseEventLogger     $responseEventLogger
     * @param SurveySession           $surveySessionService
     * @param Services\Response       $responseService
     * @param Services\Condition      $conditionService
     * @param Services\Page           $pageService
     */
    public function __construct(
        RequestHandler\Response $responseRequestHandler,
        ResponseEventLogger $responseEventLogger,
        SurveySession $surveySessionService,
        Services\Response $responseService,
        Services\Condition $conditionService,
        Services\Page $pageService
    )
    {
        $this->responseRequestHandler = $responseRequestHandler;
        $this->responseEventLogger    = $responseEventLogger;
        $this->surveySessionService   = $surveySessionService;
        $this->responseService        = $responseService;
        $this->conditionService       = $conditionService;
        $this->pageService            = $pageService;
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
        $redirectUrl = null;
        $jumpPage = null;

        $form = $this->createForm(PageType::class, null, [
            'questions' => $this->conditionService->filterQuestionsByShowCondition($page->getQuestions(), $this->responseService->answersToArray($response))
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                /** @var Document\Question $question */
                foreach ($page->getQuestions() as $question) {
                    $answers = $this->responseRequestHandler->extractAnswers($question, $form->getData());
                    $response->addAnswer(
                        new Document\ResponseAnswer($question->getQuestionId(), $answers)
                    );

                    if(!empty($question->getJumpToConditions())){
                        $questionId = $this->conditionService->applyJumpToRule($this->responseService->answersToArray($response), $question->getJumpToConditions());
                        if(!empty($questionId) && $jumpPage === null) {
                            $jumpPage = $survey->getPageByQuestion($questionId);
                        }
                    }

                    if(!empty($question->getScreenoutConditions()) && $redirectUrl === null){
                        $redirectUrl = $this->conditionService->applyScreenoutRule($this->responseService->answersToArray($response), $question->getScreenoutConditions()) ?: $redirectUrl;
                    }
                }
                $this->responseRequestHandler->saveResponse($response);

                $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_SAVED, $response);

                if(!empty($redirectUrl)) {
                    return $this->redirect($redirectUrl, 301);
                }

                $nextPage = !empty($jumpPage) ? $jumpPage : $this->pageService->getNextPage($survey, $page, $this->responseService->answersToArray($response));
                if (null === $nextPage) {

                    if ($response->isLive()) {
                        $this->surveySessionService->grantComplete($survey->getSurveyId());
                    }

                    return $this->redirectToRoute('survey.complete', ['surveyId' => $survey->getSurveyId()]);
                }

                return $this->redirectToRoute('page.index', [
                    'surveyId' => $survey->getSurveyId(),
                    'pageId'   => $nextPage->getPageId()
                ]);
            }

            $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_ERROR, $response);
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
