<?php

namespace Syno\Storm\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Form;
use Syno\Storm\Services\Survey;
use Syno\Storm\Traits\FormAware;


class SurveyController extends AbstractController implements TokenAuthenticatedController
{
    use FormAware;

    /** @var Survey */
    private $surveyService;

    /**
     * @param Survey $surveyService
     */
    public function __construct(Survey $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    /**
     * This is used to check availability of API
     *
     * @param Request $request
     *
     * @Route(
     *     "/api/survey",
     *     name="storm_api.survey.save",
     *     methods={"POST"}
     * )
     *
     * @return JsonResponse
     */
    public function save(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $survey = $this->surveyService->getNew();
        $form = $this->createForm(Form\SurveyType::class, $survey);
        $form->submit($data);
        if ($form->isValid()) {
            try {
                $this->surveyService->save($survey);
            } catch (\Exception $e) {

                return $this->json($e->getMessage(), 500);
            }

            return $this->json($survey->getId(), 201);
        }

        return $this->json($this->getFormErrors($form), 400);
    }
}
