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
        $survey = $this->getSurvey($surveyId);

        $this->surveySessionService->startSession($surveyId, 'live');

        if ($survey->getConfig()->privacyConsentEnabled) {
            return $this->redirectToRoute('survey.privacy_consent', ['surveyId' => $surveyId]);
        }

        return $this->redirectToRoute('page.display', [
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
    public function test(int $surveyId, Request $request): Response
    {
        $survey = $this->getSurvey($surveyId);

        $this->surveySessionService->startSession($surveyId, 'test');

        if ($survey->getConfig()->privacyConsentEnabled) {
            return $this->redirectToRoute('survey.privacy_consent', ['surveyId' => $surveyId]);
        }

        return $this->redirectToRoute('page.display', [
            'surveyId' => $surveyId,
            'pageId'   => $survey->getPages()->first()->getPageId()
        ]);
    }

    /**
     * @param int     $surveyId
     * @param Request $request
     *
     * @Route(
     *     "%app.route_prefix%/d/{surveyId}",
     *     name="survey.debug",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return Response|RedirectResponse
     */
    public function debug(int $surveyId, Request $request): Response
    {
        $debugToken = $request->query->getAlnum('token');
        if (empty($debugToken)) {
            throw new HttpException(400, 'Empty debug token, please provide the token in the URL');
        }

        $survey = $this->getSurvey($surveyId);
        if (false === $survey->getConfig()->debugMode) {
            throw new HttpException(403, 'This survey cannot be accessed in debug mode');
        }

        if ($debugToken !== $survey->getConfig()->debugToken) {
            throw new HttpException(403, 'Invalid debug token');
        }

        $this->surveySessionService->startSession($surveyId, 'debug');

        if ($survey->getConfig()->privacyConsentEnabled) {
            return $this->redirectToRoute('survey.privacy_consent', ['surveyId' => $surveyId]);
        }

        return $this->redirectToRoute('page.display', [
            'surveyId' => $surveyId,
            'pageId'   => $survey->getPages()->first()->getPageId()
        ]);
    }

    /**
     * @param int     $surveyId
     * @param Request $request
     *
     * @Route(
     *     "%app.route_prefix%/s/{surveyId}/privacy_consent",
     *     name="survey.privacy_consent",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return Response|RedirectResponse
     */
    public function privacyConsent(int $surveyId, Request $request)
    {
        $survey = $this->getSurvey($surveyId);
        $form = $this->createForm(PrivacyConsentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('page.display', [
                'surveyId' => $surveyId,
                'pageId'   => $survey->getPages()->first()->getId()
            ]);
        }

        return $this->render($survey->getConfig()->theme . '/survey/privacy_consent.twig');
    }

    /**
     * @param int     $surveyId
     * @param Request $request
     *
     * @Route(
     *     "%app.route_prefix%/s/{surveyId}/thank_you",
     *     name="survey.completed",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return Response|RedirectResponse
     */
    public function complete(int $surveyId)
    {
        $survey = $this->getSurvey($surveyId);

        return $this->render($survey->getConfig()->theme . '/survey/complete.twig');
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
