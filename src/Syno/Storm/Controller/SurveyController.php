<?php

namespace Syno\Storm\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class SurveyController extends AbstractController
{
    /**
     * @Route(
     *     "/{surveyId}",
     *     name="survey"
     * )
     *
     * @return RedirectResponse
     */
    public function index(): Response
    {
        return $this->render('materialize/index/index.twig');
    }

    public function privacyConsent()
    {

    }
}
