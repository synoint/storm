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
use Syno\Storm\Services;
use Syno\Storm\Services\ResponseEventLogger;
use Syno\Storm\Services\SurveyEventLogger;
use Syno\Storm\Traits\UrlTransformer;

class PageController extends AbstractController
{
    use UrlTransformer;

    /** @var RequestHandler\Response */
    private $responseRequestHandler;

    /** @var ResponseEventLogger */
    private $responseEventLogger;

    /** @var SurveyEventLogger */
    private $surveyEventLogger;

    /** @var Services\Condition */
    private $conditionService;

    /** @var Services\Page */
    private $pageService;

    /**
     * @param RequestHandler\Response $responseRequestHandler
     * @param ResponseEventLogger     $responseEventLogger
     * @param SurveyEventLogger       $surveyEventLogger
     * @param Services\Condition      $conditionService
     * @param Services\Page           $pageService
     */
    public function __construct(
        RequestHandler\Response $responseRequestHandler,
        ResponseEventLogger $responseEventLogger,
        SurveyEventLogger $surveyEventLogger,
        Services\Condition $conditionService,
        Services\Page $pageService
    )
    {
        $this->responseRequestHandler = $responseRequestHandler;
        $this->responseEventLogger    = $responseEventLogger;
        $this->surveyEventLogger      = $surveyEventLogger;
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
        $redirect      = null;
        $jumpPage      = null;

        $form = $this->createForm(
            PageType::class, null, [
            'questions' => $this->conditionService->filterQuestionsByShowCondition($page->getQuestions(), $response)
        ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                /** @var Document\Question $question */
                foreach ($page->getQuestions() as $question) {
                    $answers = $this->responseRequestHandler->extractAnswers($question, $form->getData());
                    $response->addAnswer(new Document\ResponseAnswer($question->getQuestionId(), $answers));

                    if (!empty($question->getScreenoutConditions())) {
                        $screenOutType = $this->conditionService->applyScreenoutRule($response, $question->getScreenoutConditions());
                        if (!empty($screenOutType)) {
                            $this->screenoutSurveyAndRedirect($request, $response, $survey, $screenOutType);
                        }
                    }

                    if (!empty($question->getJumpToConditions())) {
                        $questionId = $this->conditionService->applyJumpToRule($response, $question->getJumpToConditions());
                        if (!empty($questionId) && $jumpPage === null) {
                            $jumpPage = $survey->getPageByQuestion($questionId);

                            if (!empty($jumpPage)) {
                                $redirect = $this->redirectToRoute(
                                    'page.index', [
                                    'surveyId' => $survey->getSurveyId(),
                                    'pageId'   => $jumpPage->getPageId()
                                ]
                                );
                                break;
                            }
                        }
                    }
                }
                $this->responseRequestHandler->saveResponse($response);

                $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_SAVED, $response);

                if ($redirect !== null) {

                    return $redirect;
                }

                $nextPage = $this->pageService->getNextPage($survey, $page, $response);
                if (null === $nextPage) {

                    return $this->completeSurveyAndRedirect($request, $response, $survey);
                }

                return $this->redirectToRoute(
                    'page.index', [
                    'surveyId' => $survey->getSurveyId(),
                    'pageId'   => $nextPage->getPageId()
                ]
                );
            }

            $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_ERROR, $response);
        }

        return $this->render(
            $survey->getConfig()->theme . '/page/display.twig', [
            'survey'             => $survey,
            'page'               => $page,
            'response'           => $response,
            'form'               => $form->createView(),
            'backButtonDisabled' => $survey->isFirstPage($page->getPageId())
        ]
        );
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

    /**
     * @param Request           $request
     * @param Document\Response $response
     * @param Document\Survey   $survey
     *
     * @return RedirectResponse
     */
    private function completeSurveyAndRedirect(Request $request, Document\Response $response, Document\Survey $survey)
    {
        if ($response->isLive()) {
            $response->setCompleted(true);
            $this->responseRequestHandler->saveResponse($response);
            $this->responseRequestHandler->setResponse($request, $response);

            $this->responseEventLogger->log(ResponseEventLogger::SURVEY_COMPLETED, $response);
            $this->surveyEventLogger->log(SurveyEventLogger::COMPLETE, $survey);
        }

        $completeUrl = $survey->getCompleteUrl($response->getSource());

        if (!empty($completeUrl)) {
            return $this->redirect($this->populateHiddenValues($completeUrl, $response));
        }

        return $this->redirectToRoute('survey.complete', ['surveyId' => $survey->getSurveyId()]);
    }

    /**
     * @param Request           $request
     * @param Document\Response $response
     * @param Document\Survey   $survey
     * @param string   $screenoutType
     *
     * @return RedirectResponse
     */
    private function screenoutSurveyAndRedirect(Request $request, Document\Response $response, Document\Survey $survey, string $screenoutType)
    {
        if ($response->isLive()) {
            $this->responseRequestHandler->saveResponse($response);
            $this->responseRequestHandler->setResponse($request, $response);

            $this->surveyEventLogger->log(SurveyEventLogger::SCREENOUT, $survey);
        }

        $screenoutUrl = $survey->getUrl($response->getSource(), $screenoutType);

        if (!empty($screenoutUrl)) {
            return $this->redirect($this->populateHiddenValues($screenoutUrl, $response));
        }

        return $this->redirectToRoute('survey.'.$screenoutType, ['surveyId' => $survey->getSurveyId()]);
    }
}
