<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Document;
use Syno\Storm\Form\PrivacyConsentType;
use Syno\Storm\Services\Survey;
use Syno\Storm\Services\SurveySession;


class SurveyController extends AbstractController
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
     *
     * @Route(
     *     "%app.route_prefix%/s/{surveyId}",
     *     name="survey.index",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return RedirectResponse
     */
    public function index(int $surveyId): Response
    {
        $survey = $this->surveyService->getPublished($surveyId);
        if (!$survey) {
            return $this->redirectToRoute('survey.unavailable');
        }

        $surveyResponse = new Document\Response();
        $surveyResponse
            ->setSurveyId($surveyId)
            ->setSurveyVersion($survey->getVersion())
            ->setMode('live');

        $this->surveySessionService->startSession($surveyId, 'live');

        if ($survey->getConfig()->privacyConsentEnabled) {
            return $this->redirectToRoute('survey.privacy_consent', ['surveyId' => $surveyId]);
        }

        return $this->redirectToRoute('page.index', [
            'surveyId' => $surveyId,
            'pageId'   => $survey->getPages()->first()->getPageId()
        ]);
    }

    /**
     * @param int     $surveyId
     * @param Request $request
     *
     * @Route(
     *     "%app.route_prefix%/t/{surveyId}",
     *     name="survey.test",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return RedirectResponse
     */
    public function test(Request $request, int $surveyId): Response
    {
        $survey = $this->surveyService->getPublished($surveyId);
        if (!$survey) {
            return $this->redirectToRoute('survey.unavailable');
        }

        $this->surveySessionService->startSession($surveyId, 'test');

        return $this->redirectToRoute('page.index', [
            'surveyId' => $surveyId,
            'pageId'   => $survey->getPages()->first()->getPageId()
        ]);
    }

    /**
     * @param int     $surveyId
     * @param int     $versionId
     * @param Request $request
     *
     * @Route(
     *     "%app.route_prefix%/d/{surveyId}/{versionId}",
     *     name="survey.debug",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return Response|RedirectResponse
     */
    public function debug(Request $request, int $surveyId, int $versionId = null): Response
    {
        $debugToken = $request->query->getAlnum('token');
        if (empty($debugToken)) {
            throw new HttpException(400, 'Empty debug token, please provide the token in the URL');
        }

        if (null === $versionId) {
            $versionId = $this->surveyService->findLatestVersion($surveyId);
        }

        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $versionId);
        if (false === $survey->getConfig()->debugMode) {
            throw new HttpException(403, 'This survey cannot be accessed in debug mode');
        }

        if ($debugToken !== $survey->getConfig()->debugToken) {
            throw new HttpException(403, 'Invalid debug token');
        }

        $this->surveySessionService->startSession($surveyId, 'debug');

        return $this->redirectToRoute('page.index', [
            'surveyId' => $surveyId,
            'pageId'   => $survey->getPages()->first()->getPageId()
        ]);
    }

    /**
     * @param int     $surveyId
     * @param Request $request
     *
     * @Route(
     *     "%app.route_prefix%/privacy-consent/{surveyId}",
     *     name="survey.privacy_consent",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET", "POST"}
     * )
     *
     * @return Response|RedirectResponse
     */
    public function privacyConsent(Request $request, int $surveyId)
    {
        $survey = $this->surveyService->getPublished($surveyId);
        if (!$survey) {
            return $this->redirectToRoute('survey.unavailable');
        }

        $form = $this->createForm(PrivacyConsentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('page.index', [
                'surveyId' => $surveyId,
                'pageId'   => $survey->getPages()->first()->getPageId()
            ]);
        }

        return $this->render($survey->getConfig()->theme . '/survey/privacy_consent.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param int $surveyId
     *
     * @Route(
     *     "%app.route_prefix%/c/{surveyId}",
     *     name="survey.complete",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return Response|RedirectResponse
     */
    public function complete(int $surveyId)
    {
        $survey = $this->surveyService->getPublished($surveyId);
        if (!$survey) {
            return $this->redirectToRoute('survey.unavailable');
        }

        return $this->render($survey->getConfig()->theme . '/survey/complete.twig');
    }

    /**
     * @Route("%app.route_prefix%/survey/unavailable", name="survey.unavailable")
     *
     * @return Response|RedirectResponse
     */
    public function unavailable()
    {
        return $this->render(Document\Config::DEFAULT_THEME . '/survey/unavailable.twig');
    }
}
