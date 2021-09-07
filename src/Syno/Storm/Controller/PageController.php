<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    private RequestHandler\Response $responseRequestHandler;
    private ResponseEventLogger     $responseEventLogger;
    private SurveyEventLogger       $surveyEventLogger;
    private Services\Condition      $conditionService;
    private Services\Page           $pageService;

    public function __construct(
        RequestHandler\Response $responseRequestHandler,
        ResponseEventLogger $responseEventLogger,
        SurveyEventLogger $surveyEventLogger,
        Services\Condition $conditionService,
        Services\Page $pageService
    ) {
        $this->responseRequestHandler = $responseRequestHandler;
        $this->responseEventLogger    = $responseEventLogger;
        $this->surveyEventLogger      = $surveyEventLogger;
        $this->conditionService       = $conditionService;
        $this->pageService            = $pageService;
    }


    /**
     * @Route(
     *     "%app.route_prefix%/p/{surveyId}/{pageId}",
     *     name="page.index",
     *     requirements={"surveyId"="\d+", "pageId"="\d+"},
     *     methods={"GET","POST"}
     * )
     */
    public function index(
        Document\Survey $survey,
        Document\Page $page,
        Document\Response $response,
        Request $request
    ): Response {

        $questions = $this->conditionService->filterQuestionsByShowCondition($page->getQuestions(), $response);

        $respondentAnswers = $request->request->get('page') === null ?
            $response->getLastSavedAnswers() :
                $this->responseRequestHandler->extractAnswers($questions, $request->request->get('page'));

        $form = $this->createForm(
            PageType::class,
            null,
            [
                'questions'         => $questions,
                'respondentAnswers' => $respondentAnswers
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $redirect = null;
                foreach ($questions as $question) {
                    if (!$question->getScreenoutConditions()->isEmpty()) {
                        $screenOut = $this->conditionService->applyScreenoutRule($response, $question->getScreenoutConditions());
                        if (!$screenOut) {
                            continue;
                        }
                        $response->setScreenoutId($screenOut->getScreenoutId());
                        if ($screenOut->getType() === Document\ScreenoutCondition::TYPE_QUALITY_SCREENOUT) {
                            $redirect = $this->qualityScreenOut($survey, $response, $request);
                        } else {
                            $redirect = $this->screenOut($survey, $response, $request);
                        }
                        break;
                    }
                }

                foreach ($questions as $question) {
                    if (!$question->getJumpToConditions()->isEmpty()) {
                        $jump = $this->conditionService->applyJumpRule($response, $question->getJumpToConditions());
                        if (!$jump) {
                            continue;
                        }

                        if (Document\JumpToCondition::DESTINATION_TYPE_END_OF_SURVEY == $jump->getDestinationType()) {
                            $redirect = $this->completeSurveyAndRedirect($survey, $response, $request);
                        } elseif (Document\JumpToCondition::DESTINATION_TYPE_QUESTION == $jump->getDestinationType()) {
                            $jumpPage = $survey->getPageByQuestion($jump->getDestination());
                            if ($jumpPage) {
                                $redirect = $this->redirectToRoute(
                                    'page.index', [
                                                    'surveyId' => $survey->getSurveyId(),
                                                    'pageId' => $jumpPage->getPageId()
                                                ]
                                );
                            } else {
                                $redirect = $this->unavailable();
                            }
                        } else {
                            throw new \UnexpectedValueException('Unknown jump type');
                        }
                        break;
                    }
                }

                if ($redirect !== null) {
                    return $redirect;
                }

                $nextPage = $this->pageService->getNextPage($survey, $page, $response);
                if (null === $nextPage) {
                    return $this->completeSurveyAndRedirect($survey, $response, $request);
                }

                $attr = [
                    'surveyId' => $survey->getSurveyId(),
                    'pageId'   => $nextPage->getPageId()
                ];
                if ($request->query->has($request->getSession()->getName())) {
                    $attr[$request->getSession()->getName()] = $request->getSession()->getId();
                }

                return $this->redirectToRoute('page.index', $attr);
            }

            $this->responseEventLogger->log(ResponseEventLogger::ANSWERS_ERROR, $response);
        } else {
            $this->saveResponseProgress($response, $page);
        }

        return $this->render(
            $survey->getConfig()->theme . '/page/display.twig', [
                'survey' => $survey,
                'page' => $page,
                'questions' => $questions,
                'response' => $response,
                'form' => $form->createView(),
                'backButtonDisabled' => $survey->isFirstPage($page->getPageId())
            ]
        );
    }

    /**
     * @Route("%app.route_prefix%/p/unavailable", name="page.unavailable")
     */
    public function unavailable(): Response
    {
        return $this->render(Document\Config::DEFAULT_THEME . '/page/unavailable.twig');
    }




}
