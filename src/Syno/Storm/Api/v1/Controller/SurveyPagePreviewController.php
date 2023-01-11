<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Api\Controller\TokenAuthenticatedController;
use Syno\Storm\Api\v1\Form;
use Syno\Storm\Api\v1\Http\ApiResponse;
use Syno\Storm\Document\SurveyPreview;
use Syno\Storm\Form\PageType;
use Syno\Storm\Services;
use Syno\Storm\Services\ResponseEvent;
use Syno\Storm\Services\ResponseSessionManager;
use Syno\Storm\Traits\FormAware;
use Syno\Storm\Traits\JsonRequestAware;

/**
 * @Route("/api/v1/survey")
 */
class SurveyPagePreviewController extends AbstractController implements TokenAuthenticatedController
{
    use FormAware;
    use JsonRequestAware;

    private ResponseEvent $responseEventService;

    private Services\Survey $surveyService;

    private ResponseSessionManager $responseSessionManager;

    public function __construct(
        ResponseEvent $responseEventService,
        Services\Survey $surveyService,
        ResponseSessionManager $responseSessionManager
    ) {
        $this->responseEventService   = $responseEventService;
        $this->surveyService          = $surveyService;
        $this->responseSessionManager = $responseSessionManager;
    }

    /**
     * @Route(
     *     "/page-preview",
     *     name="storm_api.v1.survey.page_preview",
     *     methods={"POST"}
     * )
     */
    public function preview(Request $request): JsonResponse
    {
        $surveyPreview = new SurveyPreview();

        $form = $this->createForm(Form\SurveyPreviewType::class, $surveyPreview);
        $form->submit($this->getJson($request));

        if ($form->isValid()) {
            $form = $this->createForm(
                PageType::class,
                null,
                [
                    'questions' => $surveyPreview->getPages()->first()->getQuestions(),
                ]
            );

            $html = $this->render('b4/page/preview/display.twig', [
                'survey'             => $surveyPreview,
                'page'               => $surveyPreview->getPages()->first(),
                'questions'          => $surveyPreview->getPages()->first()->getQuestions(),
                'form'               => $form->createView(),
            ]);

            return $this->json(
                $html,
                HttpResponse::HTTP_OK,
                ['Content-Type' => 'application/json;charset=UTF-8']
            );
        }

        return new ApiResponse('Page preview failed!', null, $this->getFormErrors($form), HttpResponse::HTTP_BAD_REQUEST);
    }
}
