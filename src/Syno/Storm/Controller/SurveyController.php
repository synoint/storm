<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Document;
use Syno\Storm\Form\PrivacyConsentType;
use Syno\Storm\Services\Survey;


class SurveyController extends AbstractController
{
    /** @var Survey */
    private $surveyService;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * @param Survey                   $surveyService
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Survey $surveyService, EventDispatcherInterface $dispatcher)
    {
        $this->surveyService = $surveyService;
        $this->dispatcher    = $dispatcher;
    }

    /**
     * @param Document\Survey $survey
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
    public function index(Document\Survey $survey): Response
    {
        if ($survey->getConfig()->privacyConsentEnabled) {
            return $this->redirectToRoute('survey.privacy_consent', ['surveyId' => $survey->getSurveyId()]);
        }

        return $this->redirectToRoute('page.index', [
            'surveyId' => $survey->getSurveyId(),
            'pageId'   => $survey->getPages()->first()->getPageId()
        ]);
    }

    /**
     * @param Document\Survey $survey
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
    public function test(Document\Survey $survey): Response
    {
        return $this->redirectToRoute('page.index', [
            'surveyId' => $survey->getSurveyId(),
            'pageId'   => $survey->getPages()->first()->getPageId()
        ]);
    }

    /**
     * @param Request         $request
     * @param Document\Survey $survey
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
    public function debug(Request $request, Document\Survey $survey): Response
    {
        $debugToken = $request->query->getAlnum('token');
        if (empty($debugToken)) {
            throw new HttpException(400, 'Empty debug token, please provide the token in the URL');
        }

        if (false === $survey->getConfig()->debugMode) {
            throw new HttpException(403, 'This survey cannot be accessed in debug mode');
        }

        if ($debugToken !== $survey->getConfig()->debugToken) {
            throw new HttpException(403, 'Invalid debug token');
        }

        return $this->redirectToRoute('page.index', [
            'surveyId' => $survey->getSurveyId(),
            'pageId'   => $survey->getPages()->first()->getPageId()
        ]);
    }

    /**
     * @param Document\Survey $survey
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
    public function privacyConsent(Document\Survey $survey, Request $request)
    {
       $form = $this->createForm(PrivacyConsentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('page.index', [
                'surveyId' => $survey->getSurveyId(),
                'pageId'   => $survey->getPages()->first()->getPageId()
            ]);
        }

        return $this->render($survey->getConfig()->theme . '/survey/privacy_consent.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Document\Survey $survey
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
    public function complete(Document\Survey $survey)
    {
        return $this->render($survey->getConfig()->theme . '/survey/complete.twig');
    }

    /**
     * @Route("%app.route_prefix%/s/unavailable", name="survey.unavailable")
     *
     * @return Response|RedirectResponse
     */
    public function unavailable()
    {
        return $this->render(Document\Config::DEFAULT_THEME . '/survey/unavailable.twig');
    }
}
