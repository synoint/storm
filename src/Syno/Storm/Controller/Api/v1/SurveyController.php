<?php

namespace Syno\Storm\Controller\Api\v1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Form;
use Syno\Storm\Services\Survey;
use Syno\Storm\Traits\FormAware;
use Syno\Storm\Controller\Api\TokenAuthenticatedController;

/**
 * @Route("/api/v1/survey")
 */
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
     *     "",
     *     name="storm_api.survey.create",
     *     methods={"POST"}
     * )
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        //$this->removeVersionIfExists($data);

        $survey = $this->surveyService->getNew();
        $form = $this->createForm(Form\SurveyType::class, $survey);
        $form->submit($data);
        if ($form->isValid()) {
            $this->surveyService->save($survey);

            return $this->json($survey->getId(), 201);
        }

        return $this->json($this->getFormErrors($form), 400);
    }

    /**
     * @param int $stormMakerSurveyId
     * @param int $version
     *
     * @Route(
     *     "/{stormMakerSurveyId}/{version}",
     *     name="storm_api.survey.retrieve",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return JsonResponse
     */
    public function retrieve(int $stormMakerSurveyId, int $version)
    {
        $survey = $this->surveyService->findByStormMakerIdAndVersion($stormMakerSurveyId, $version);
        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $stormMakerSurveyId, $version),
                404
            );
        }

        return $this->json($survey);
    }

    /**
     * @param int $stormMakerSurveyId
     * @param int $version
     *
     * @Route(
     *     "/{stormMakerSurveyId}/{version}",
     *     name="storm_api.survey.delete",
     *     requirements={"id"="\d+"},
     *     methods={"DELETE"}
     * )
     *
     * @return JsonResponse
     */
    public function delete(int $stormMakerSurveyId, int $version)
    {
        $survey = $this->surveyService->findByStormMakerIdAndVersion($stormMakerSurveyId, $version);
        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $stormMakerSurveyId, $version),
                404
            );
        }

        $this->surveyService->delete($survey);

        return $this->json('ok');
    }

    /**
     * @param array $params
     */
    protected function removeVersionIfExists(array $params)
    {
        if (!empty($params['stormMakerSurveyId']) && !empty($params['version'])) {
            $survey = $this->surveyService->findByStormMakerIdAndVersion($params['stormMakerSurveyId'], $params['version']);
            if ($survey) {
                $this->surveyService->delete($survey);
            }
        }
    }


}
