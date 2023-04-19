<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Document;
use Syno\Storm\Form\PageType;
use Syno\Storm\Services\ResponseDataLayer;
use Syno\Storm\Services\ResponseSessionManager;

class PageController extends AbstractController
{
    private ResponseSessionManager $responseSessionManager;
    private ResponseDataLayer      $responseDataLayer;

    public function __construct(ResponseSessionManager $responseSessionManager, ResponseDataLayer $responseDataLayer)
    {
        $this->responseSessionManager = $responseSessionManager;
        $this->responseDataLayer      = $responseDataLayer;
    }

    /**
     * @Route(
     *     "%app.route_prefix%/p/{surveyId}/{pageId}",
     *     name="page.index",
     *     requirements={"surveyId"="\d+", "pageId"="\d+"},
     *     methods={"GET","POST"}
     * )
     */
    public function index(Document\Survey $survey, Document\Page $page, Request $request): Response
    {
        $filteredQuestions = $this->responseSessionManager->getQuestions();

        $form = $this->createForm(
            PageType::class,
            null,
            [
                'questions' => $filteredQuestions,
                'answers'   => $this->responseSessionManager->getAnswerMap($request->request->get('p'))
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $this->responseSessionManager->saveAnswers($form->getData(), $filteredQuestions);

                $redirect = $this->responseSessionManager->redirectOnScreenOut();
                if (!$redirect) {
                    $redirect = $this->responseSessionManager->redirectOnJump();
                }

                if ($redirect) {
                    return $redirect;
                }

                // get next page or what...
                return $this->responseSessionManager->advance();
            }

            $this->responseSessionManager->answeredWithErrors();

        } else {
            $this->responseSessionManager->saveProgress();
        }

        return $this->render($survey->getConfig()->getTheme() . '/page/display.twig', [
            'survey'             => $survey,
            'page'               => $page,
            'questions'          => $filteredQuestions,
            'response'           => $this->responseSessionManager->getResponse(),
            'form'               => $form->createView(),
            'backButtonDisabled' => $this->responseSessionManager->isFirstPage($page->getPageId()),
            'isLastPage'         => $this->responseSessionManager->isLastPage($page->getPageId()),
            'responseDataLayer'  => $this->responseDataLayer->getData(),
        ]);
    }

    /**
     * @Route("%app.route_prefix%/p/unavailable", name="page.unavailable")
     */
    public function unavailable(): Response
    {
        return $this->render(Document\Config::DEFAULT_THEME . '/page/unavailable.twig');
    }
}
