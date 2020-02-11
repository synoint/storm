<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Api\Controller\TokenAuthenticatedController;
use Syno\Storm\Api\v1\Form;
use Syno\Storm\Api\v1\Http\ApiResponse;
use Syno\Storm\Services\Survey;
use Syno\Storm\Services\SurveyStats;
use Syno\Storm\Traits\FormAware;
use Syno\Storm\Traits\JsonRequestAware;

/**
 * @Route("/api/v1/survey")
 */
class SurveyController extends AbstractController implements TokenAuthenticatedController
{
    use FormAware;
    use JsonRequestAware;

    /** @var Survey */
    private $surveyService;

    /** @var SurveyStats */
    private $surveyStatsService;

    /**
     * @param Survey      $surveyService
     * @param SurveyStats $surveyStatsService
     */
    public function __construct(Survey $surveyService, SurveyStats $surveyStatsService)
    {
        $this->surveyService      = $surveyService;
        $this->surveyStatsService = $surveyStatsService;
    }


    /**
     * @param Request $request
     *
     * @Route(
     *     "",
     *     name="storm_api.v1.survey.create",
     *     methods={"POST"}
     * )
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $data = $this->getJson($request);
        $this->removeVersionIfExists($data);

        $survey = $this->surveyService->getNew();

        $form = $this->createForm(Form\SurveyType::class, $survey);
        $form->submit($data);
        if ($form->isValid()) {
            $this->surveyService->save($survey);

            $this->surveyStatsService->save(
                $this->surveyStatsService->getNew($survey->getSurveyId(), $survey->getVersion())
            );

            return $this->json($survey->getId(), 201);
        }

        return new ApiResponse('Survey creation failed!', null, $this->getFormErrors($form), 400);
    }

    /**
     * @param int $surveyId
     * @param int $version
     *
     * @Route(
     *     "/{surveyId}/{version}",
     *     name="storm_api.v1.survey.retrieve",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return JsonResponse
     */
    public function retrieve(int $surveyId, int $version)
    {
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);
        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }

        return $this->json($survey);
    }

    /**
     * @param int $surveyId
     * @param int $version
     *
     * @Route(
     *     "/{surveyId}/{version}/stats",
     *     name="storm_api.v1.survey.stats",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return JsonResponse
     */
    public function stats(int $surveyId, int $version)
    {
        $surveyStats = $this->surveyStatsService->findBySurveyIdAndVersion($surveyId, $version);
        if (!$surveyStats) {
            return $this->json(
                sprintf('Survey stats for survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }

        return $this->json($surveyStats);
    }

    /**
     * @param int $surveyId
     * @param int $version
     *
     * @Route(
     *     "/{surveyId}/{version}",
     *     name="storm_api.v1.survey.delete",
     *     requirements={"id"="\d+", "version"="\d+"},
     *     methods={"DELETE"}
     * )
     *
     * @return JsonResponse
     */
    public function delete(int $surveyId, int $version)
    {
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);
        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }

        $this->surveyService->delete($survey);
        $this->surveyStatsService->delete($surveyId, $version);

        return $this->json('ok');
    }

    /**
     * @param int $surveyId
     * @param int $version
     *
     * @Route(
     *     "/{surveyId}/{version}/publish",
     *     name="storm_api.v1.survey.publish",
     *     requirements={"id"="\d+", "version"="\d+"},
     *     methods={"PUT"}
     * )
     *
     * @return JsonResponse
     */
    public function publish(int $surveyId, int $version)
    {
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);
        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }
        $this->surveyService->publish($survey);


        return $this->json('ok');
    }

    /**
     * @param int    $surveyId
     * @param int    $version
     * @param string $toggle
     *
     * @Route(
     *     "/{surveyId}/{version}/debug/{toggle}",
     *     name="storm_api.v1.survey.debug",
     *     requirements={"id"="\d+", "version"="\d+", "toggle"="enable|disable"},
     *     methods={"PUT"}
     * )
     *
     * @return JsonResponse
     */
    public function toggleDebugMode(int $surveyId, int $version, string $toggle)
    {
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);
        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }

        if ('enable' === $toggle) {
            $token = $this->surveyService->enableDebugMode($survey);

            return $this->json($token);
        }

        if ('disable' === $toggle) {
            $this->surveyService->disableDebugMode($survey);

            return $this->json('ok');
        }

        return $this->json('Unknown action', 400);
    }

    /**
     * @param array $params
     */
    protected function removeVersionIfExists(array $params)
    {
        if (!empty($params['surveyId']) && !empty($params['version'])) {
            $survey = $this->surveyService->findBySurveyIdAndVersion($params['surveyId'], $params['version']);
            if ($survey) {
                $this->surveyService->delete($survey);
                $this->surveyStatsService->delete($params['surveyId'], $params['version']);
            }
        }
    }

}
