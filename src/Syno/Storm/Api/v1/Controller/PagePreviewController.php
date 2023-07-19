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
use Syno\Storm\Document\PagePreview;
use Syno\Storm\Form\PageType;
use Syno\Storm\Services;
use Syno\Storm\Services\ResponseEvent;
use Syno\Storm\Services\ResponseSessionManager;
use Syno\Storm\Traits\FormAware;
use Syno\Storm\Traits\JsonRequestAware;

/**
 * @Route("/api/v1/page")
 */
class PagePreviewController extends AbstractController implements TokenAuthenticatedController
{
    use FormAware;
    use JsonRequestAware;

    private Services\Translation $translationService;

    public function __construct(
        Services\Translation $translationService
    ) {
        $this->translationService = $translationService;
    }

    /**
     * @Route(
     *     "/preview",
     *     name="storm_api.v1.page.preview",
     *     methods={"POST"}
     * )
     */
    public function preview(Request $request): JsonResponse
    {
        $pagePreview = new PagePreview();

        $form = $this->createForm(Form\PagePreviewType::class, $pagePreview);
        $form->submit($this->getJson($request));

        if ($form->isValid()) {
            $page = $pagePreview->getPage();
            $this->translationService->setPageLocale($page, $pagePreview->getLocale());

            $form = $this->createForm(
                PageType::class,
                null,
                [
                    'questions' => $page->getQuestions(),
                ]
            );

            $html = $this->render('b4/page/preview/display.twig', [
                'survey'    => $pagePreview,
                'page'      => $page,
                'questions' => $page->getQuestions(),
                'form'      => $form->createView(),
            ]);

            return $this->json($html, HttpResponse::HTTP_OK);
        }

        return new ApiResponse('Page preview failed!', null, $this->getFormErrors($form),
                               HttpResponse::HTTP_BAD_REQUEST);
    }
}
