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
use Syno\Storm\Services\ResponseSessionManager;
use Syno\Storm\Services\SurveyEndPage;
use Syno\Storm\Services\PrivacyConsentCookie;

class SurveyController extends AbstractController
{
    private SurveyEndPage $surveyEndPageService;
    private ResponseSessionManager $responseSessionManager;
    private PrivacyConsentCookie   $privacyConsentCookie;
    
    public function __construct(
        SurveyEndPage $surveyEndPageService,
        ResponseSessionManager $responseSessionManager,
        PrivacyConsentCookie $privacyConsentCookie
    ) {
        $this->surveyEndPageService   = $surveyEndPageService;
        $this->responseSessionManager = $responseSessionManager;
        $this->privacyConsentCookie   = $privacyConsentCookie;
    }
    
    /**
     * @Route(
     *     "%app.route_prefix%/s/{surveyId}",
     *     name="survey.index",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function index(Request $request, Document\Survey $survey): RedirectResponse
    {
        if ($survey->getConfig()->isPrivacyConsentEnabled() && !$this->privacyConsentCookie->isCookieSet($request)) {
            return $this->redirectToRoute('survey.privacy_consent', ['surveyId' => $survey->getSurveyId()]);
        }
        
        return $this->responseSessionManager->redirectToFirstPage();
    }
    
    /**
     * @Route(
     *     "%app.route_prefix%/t/{surveyId}",
     *     name="survey.test",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function test(): RedirectResponse
    {
        return $this->responseSessionManager->redirectToFirstPage();
    }
    
    /**
     * @Route(
     *     "%app.route_prefix%/d/{surveyId}/{versionId}",
     *     name="survey.debug",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function debug(Request $request, Document\Survey $survey): RedirectResponse
    {
        $debugToken = $request->query->getAlnum('token');
        if (empty($debugToken)) {
            throw new HttpException(400, 'Empty debug token, please provide the token in the URL');
        }
        
        if (!$survey->getConfig()->isDebugMode()) {
            throw new HttpException(403, 'This survey cannot be accessed in debug mode');
        }
        
        if ($debugToken !== $survey->getConfig()->getDebugToken()) {
            throw new HttpException(403, 'Invalid debug token');
        }
        
        return $this->responseSessionManager->redirectToFirstPage();
    }
    
    /**
     * @Route(
     *     "%app.route_prefix%/privacy-consent/{surveyId}",
     *     name="survey.privacy_consent",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET", "POST"}
     * )
     *
     * @return Response|RedirectResponse
     */
    public function privacyConsent(Document\Survey $survey, Request $request): Response
    {
        $form = $this->createForm(PrivacyConsentType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->responseSessionManager->redirectToFirstPage();
            
            return $this->privacyConsentCookie->setCookie($response);
        }
        
        return $this->render($survey->getConfig()->getTheme() . '/survey/privacy_consent.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route(
     *     "%app.route_prefix%/c/{surveyId}",
     *     name="survey.complete",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function complete(Request $request, Document\Survey $survey): Response
    {
        return $this->render($survey->getConfig()->getTheme() . '/survey/complete.twig', [
            'survey'        => $survey,
            'customContent' => $this->surveyEndPageService->getEndPageContentByLocale(
                $survey,
                $request->getLocale(), Document\SurveyEndPage::TYPE_COMPLETE
            )
        ]);
    }
    
    /**
     * @Route(
     *     "%app.route_prefix%/sc/{surveyId}",
     *     name="survey.screenout",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function screenOut(Request $request, Document\Survey $survey): Response
    {
        return $this->render($survey->getConfig()->getTheme() . '/survey/screenout.twig', [
            'survey'        => $survey,
            'customContent' => $this->surveyEndPageService->getEndPageContentByLocale(
                $survey,
                $request->getLocale(), Document\SurveyEndPage::TYPE_SCREENOUT
            )
        ]);
    }
    
    /**
     * @Route(
     *     "%app.route_prefix%/qsc/{surveyId}",
     *     name="survey.quality_screenout",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function qualityScreenOut(Request $request, Document\Survey $survey): Response
    {
        return $this->render($survey->getConfig()->getTheme() . '/survey/quality_screenout.twig', [
            'survey'        => $survey,
            'customContent' => $this->surveyEndPageService->getEndPageContentByLocale(
                $survey,
                $request->getLocale(), Document\SurveyEndPage::TYPE_QUALITY_SCREENOUT
            )
        ]);
    }
    
    /**
     * @Route(
     *     "%app.route_prefix%/qf/{surveyId}",
     *     name="survey.quota_full",
     *     requirements={"surveyId"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function quotaFull(Request $request, Document\Survey $survey): Response
    {
        return $this->render($survey->getConfig()->getTheme() . '/survey/screenout.twig', [
            'survey'        => $survey,
            'customContent' => $this->surveyEndPageService->getEndPageContentByLocale(
                $survey,
                $request->getLocale(), Document\SurveyEndPage::TYPE_QUOTA_FULL
            )
        ]);
    }
}
