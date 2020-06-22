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
        $redirect = null;
        $questions = $this->conditionService->filterQuestionsByShowCondition($page->getQuestions(), $response);

        $respondentAnswers = $request->request->get('page') === null ? $response->getLastSavedAnswers() : $this->responseRequestHandler->extractAnswers($questions, $request->request->get('page'));

        $form = $this->createForm(PageType::class, null,
            [
                'questions'         => $questions,
                'respondentAnswers' => $respondentAnswers
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                /** @var Document\Question $question */
                foreach ($questions as $question) {
                    $answers = $this->responseRequestHandler->extractQuestionAnswers($question, $form->getData());
                    $response->addAnswer(new Document\ResponseAnswer($question->getQuestionId(), $answers));

                    if (!empty($question->getScreenoutConditions())) {
                        $triggeredScreenout = $this->conditionService->applyScreenoutRule($response, $question->getScreenoutConditions());
                        if (!empty($triggeredScreenout)) {
                            $redirect = $this->screenoutSurveyAndRedirect($request, $response, $survey, $triggeredScreenout);
                            break;
                        }
                    }

                    if (!empty($question->getJumpToConditions())) {
                        $firedJumpCondition = $this->conditionService->applyJumpToRule($response, $question->getJumpToConditions());
                        if (!empty($firedJumpCondition)) {
                            switch ($firedJumpCondition->getDestinationType()) {
                                case Document\JumpToCondition::DESTINATION_TYPE_END_OF_SURVEY:
                                    $redirect = $this->completeSurveyAndRedirect($request, $response, $survey);
                                    break;
                                case Document\JumpToCondition::DESTINATION_TYPE_QUESTION:
                                    $redirect = $this->redirectSurveyToJump($survey, $firedJumpCondition->getDestination());
                                    break;
                            }

                            break;
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
                    'page.index',
                    [
                        'surveyId' => $survey->getSurveyId(),
                        'pageId'   => $nextPage->getPageId()
                    ]
                );
            }

            $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_ERROR, $response);
        } else {
            $this->saveResponseProgress($response, $page);
        }

        return $this->render(
            $survey->getConfig()->theme . '/page/display.twig', [
            'survey'             => $survey,
            'page'               => $page,
            'questions'          => $questions,
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
     * @param Document\Response $response
     * @param Document\Page     $page
     */
    private function saveResponseProgress(Document\Response $response, Document\Page $page)
    {
        if ($response->getPageId() !== $page->getPageId()) {
            $response->setPageId($page->getPageId());
            $response->setPageCode($page->getCode());
            $this->responseRequestHandler->saveResponse($response);

            $this->responseEventLogger->log(ResponseEventLogger::PAGE_ENTERED, $response);
        }
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
        $response->setCompleted(true);
        $this->responseRequestHandler->saveResponse($response);
        $this->responseRequestHandler->setResponse($request, $response);

        $this->responseEventLogger->log(ResponseEventLogger::SURVEY_COMPLETED, $response);
        $this->surveyEventLogger->logComplete($response, $survey);

        $completeUrl = $survey->getCompleteUrl($response->getSource());

        if (!empty($completeUrl)) {
            return $this->redirect($this->populateParameters($completeUrl, $response));
        }

        return $this->redirectToRoute('survey.complete', ['surveyId' => $survey->getSurveyId()]);
    }

    /**
     * @param Request $request
     * @param Document\Response $response
     * @param Document\Survey   $survey
     * @param Document\ScreenoutCondition   $triggeredScreenout
     *
     * @return RedirectResponse
     */
    private function screenoutSurveyAndRedirect(Request $request, Document\Response $response, Document\Survey $survey, Document\ScreenoutCondition $triggeredScreenout)
    {
        switch ($triggeredScreenout->getType()) {
            case Document\ScreenoutCondition::TYPE_QUALITY_SCREENOUT:

                $response->setQualityScreenedOut(true);

                $responseLogType    = ResponseEventLogger::SURVEY_SCREENOUTED;
                $logType            = SurveyEventLogger::QUALITY_SCREENOUT;
                $url                = $survey->getQualityScreenoutUrl($response->getSource());
                $redirect           = $this->redirectToRoute('survey.quality_screenout', ['surveyId' => $survey->getSurveyId()]);
                break;
            default:

                $response->setScreenedOut(true);

                $responseLogType    = ResponseEventLogger::SURVEY_QUALITY_SCREENOUTED;
                $logType            = SurveyEventLogger::SCREENOUT;
                $url                = $survey->getScreenoutUrl($response->getSource());
                $redirect           = $this->redirectToRoute('survey.screenout', ['surveyId' => $survey->getSurveyId()]);
                break;
        }

        $response->setScreenoutId($triggeredScreenout->getScreenoutId());

        $this->responseRequestHandler->saveResponse($response);
        $this->responseRequestHandler->setResponse($request, $response);

        $this->responseEventLogger->log($responseLogType, $response);
        $this->surveyEventLogger->log($logType, $survey);

        if (!empty($url)) {
            $redirect = $this->redirect($this->populateParameters($url, $response));
        }

        return $redirect;
    }

    /**
     * @param Document\Survey $survey
     * @param int $questionId
     *
     * @return RedirectResponse
     */
    private function redirectSurveyToJump(Document\Survey $survey, int $questionId)
    {
        $jumpPage = $survey->getPageByQuestion($questionId);

        if (!empty($jumpPage)) {
            return $this->redirectToRoute(
                'page.index', [
                    'surveyId' => $survey->getSurveyId(),
                    'pageId'   => $jumpPage->getPageId()
                ]
            );
        }

        return null;
    }
}
