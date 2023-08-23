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
use Syno\Storm\Document;
use Syno\Storm\Services\Randomization;
use Syno\Storm\Services\Response;
use Syno\Storm\Services\ResponseEvent;
use Syno\Storm\Services\Survey;
use Syno\Storm\Services\SurveyConfig;
use Syno\Storm\Services\SurveyEvent;
use Syno\Storm\Services\SurveyEventLogger;
use Syno\Storm\Services\SurveyPath;
use Syno\Storm\Traits\FormAware;
use Syno\Storm\Traits\JsonRequestAware;

/**
 * @Route("/api/v1/survey")
 */
class SurveyController extends AbstractController implements TokenAuthenticatedController
{
    use FormAware;
    use JsonRequestAware;

    private Response          $responseService;
    private ResponseEvent     $responseEventService;
    private Survey            $surveyService;
    private SurveyEvent       $surveyEventService;
    private SurveyEventLogger $surveyEventLoggerService;
    private SurveyPath        $surveyPathService;
    private Randomization     $randomizationService;
    private SurveyConfig      $surveyConfigService;

    public function __construct(
        Response          $responseService,
        ResponseEvent     $responseEventService,
        Survey            $surveyService,
        SurveyEvent       $surveyEventService,
        SurveyEventLogger $surveyEventLoggerService,
        SurveyPath        $surveyPathService,
        Randomization     $randomizationService,
        SurveyConfig      $surveyConfigService
    )
    {
        $this->responseService          = $responseService;
        $this->responseEventService     = $responseEventService;
        $this->surveyService            = $surveyService;
        $this->surveyEventService       = $surveyEventService;
        $this->surveyEventLoggerService = $surveyEventLoggerService;
        $this->surveyPathService        = $surveyPathService;
        $this->randomizationService     = $randomizationService;
        $this->surveyConfigService      = $surveyConfigService;
    }

    /**
     * @Route(
     *     "",
     *     name="storm_api.v1.survey.create",
     *     methods={"POST"}
     * )
     */
    public function create(Request $request): JsonResponse
    {
        $data = $this->getJson($request);

        if (!empty($data['surveyId']) && !empty($data['version'])) {
            $survey = $this->surveyService->findBySurveyIdAndVersion($data['surveyId'], $data['version']);
            if ($survey) {
                $this->deleteSurvey($survey);
            }
        }

        $survey = $this->surveyService->getNew();

        $form = $this->createForm(Form\SurveyType::class, $survey);
        $form->submit($data);
        if ($form->isValid()) {
            $this->surveyService->save($survey);
            $this->surveyEventLoggerService->log(SurveyEventLogger::SURVEY_CREATED, $survey);

            return $this->json($survey->getId(), 201);
        }

        return new ApiResponse('Survey creation failed!', null, $this->getFormErrors($form), 400);
    }

    /**
     * @Route(
     *     "/{surveyId}",
     *     name="storm_api.v1.survey.retrieve.all_version",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function retrieveAllVersions(int $surveyId): JsonResponse
    {
        $survey = $this->surveyService->find($surveyId);
        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d was not found', $surveyId),
                404
            );
        }

        return $this->json($survey);
    }

    /**
     * @Route(
     *     "/{surveyId}/{version}",
     *     name="storm_api.v1.survey.retrieve",
     *     requirements={"id"="\d+", "version"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function retrieve(int $surveyId, int $version): JsonResponse
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
     * @Route(
     *     "/{surveyId}/{version}",
     *     name="storm_api.v1.survey.delete",
     *     requirements={"id"="\d+", "version"="\d+"},
     *     methods={"DELETE"}
     * )
     */
    public function delete(Request $request, int $surveyId, int $version): JsonResponse
    {
        $deleteOnlyUnused  = $request->get('deleteOnlyUnused');
        $survey            = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);
        $lastSurveyVersion = $this->surveyService->findLatestVersion($surveyId);

        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }

        if($deleteOnlyUnused) {
            if ($survey->isPublished()) {
                return $this->json(
                    sprintf('Published survey (surveyId: %d, version: %d) can not be deleted', $surveyId, $version),
                    HttpResponse::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            if ($lastSurveyVersion == $survey->getVersion()) {
                return $this->json(
                    sprintf('Survey (surveyId: %d, version: %d) Latest survey version can not be deleted', $surveyId, $version),
                    HttpResponse::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $responses = $this->responseService->liveCountBySurveyAndVersion($survey->getSurveyId(), $survey->getVersion());

            if ($responses) {
                return $this->json(
                    sprintf('Survey (surveyId: %d, version: %d) can not be deleted because this survey version has responses', $surveyId, $version),
                    HttpResponse::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        $this->deleteSurvey($survey);

        return $this->json(sprintf('Survey (surveyId: %d, version: %d) deleted', $surveyId, $version));
    }

    /**
     * @Route(
     *     "/{surveyId}/{version}/publish",
     *     name="storm_api.v1.survey.publish",
     *     requirements={"id"="\d+", "version"="\d+"},
     *     methods={"PUT"}
     * )
     */
    public function publish(int $surveyId, int $version): JsonResponse
    {
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);
        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }
        $this->surveyService->publish($survey);

        $this->surveyEventLoggerService->log(SurveyEventLogger::SURVEY_PUBLISHED, $survey);

        return $this->json('ok');
    }

    /**
     * @Route(
     *     "/{surveyId}/{version}/unpublish",
     *     name="storm_api.v1.survey.unpublish",
     *     requirements={"id"="\d+", "version"="\d+"},
     *     methods={"PUT"}
     * )
     */
    public function unpublish(int $surveyId, int $version): JsonResponse
    {
        $survey = $this->surveyService->findBySurveyIdAndVersion($surveyId, $version);
        if (!$survey) {
            return $this->json(
                sprintf('Survey with ID: %d, version: %d was not found', $surveyId, $version),
                404
            );
        }
        $survey->setPublished(false);
        $this->surveyService->save($survey);

        $this->surveyEventLoggerService->log(SurveyEventLogger::SURVEY_UNPUBLISHED, $survey);

        return $this->json('ok');
    }

    /**
     * @Route(
     *     "/{surveyId}/{version}/debug/{toggle}",
     *     name="storm_api.v1.survey.debug",
     *     requirements={"id"="\d+", "version"="\d+", "toggle"="enable|disable"},
     *     methods={"PUT"}
     * )
     */
    public function toggleDebugMode(int $surveyId, int $version, string $toggle): JsonResponse
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
     * @Route(
     *     "/{surveyId}/events",
     *     name="storm_api.v1.survey.events",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function events(int $surveyId, Request $request): JsonResponse
    {
        return $this->json(
            $this->surveyEventService->getAllBySurveyId(
                $surveyId,
                $request->query->getInt('limit', 1000),
                $request->query->getInt('offset')
            )
        );
    }

    /**
     * @Route(
     *     "/{surveyId}/events/last/date",
     *     name="storm_api.v1.survey.event.last.date",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     */
    public function eventLastDate(int $surveyId): JsonResponse
    {
        return $this->json($this->responseEventService->getLastDate($surveyId));
    }

    /**
     * @Route(
     *     "/{surveyId}/config",
     *     name="storm_api.v1.survey.save_config",
     *     requirements={"id"="\d+"},
     *     methods={"POST"}
     * )
     */
    public function saveConfig(Request $request, int $surveyId): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data) {
            foreach ($data as $key => $value) {
                $surveyConfig = $this->surveyConfigService->findBySurveyIdAndKey($surveyId, $key);

                if (!$surveyConfig) {
                    $surveyConfig = new Document\SurveyConfig();
                    $surveyConfig->setSurveyId($surveyId);
                    $surveyConfig->setKey($key);
                }

                $surveyConfig->setValue($value);

                $this->surveyConfigService->save($surveyConfig);
            }
        }

        return $this->json('ok');
    }

    /**
     * @Route(
     *     "/{surveyId}/config",
     *     name="storm_api.v1.survey.delete_config",
     *     requirements={"id"="\d+"},
     *     methods={"DELETE"}
     * )
     */
    public function deleteConfig(Request $request, int $surveyId): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if ($data) {
            foreach ($data as $key) {
                $surveyConfig = $this->surveyConfigService->findBySurveyIdAndKey($surveyId, $key);

                if ($surveyConfig) {
                    $this->surveyConfigService->delete($surveyConfig);
                }
            }
        }

        return $this->json('ok');
    }

    protected function deleteSurvey(Document\Survey $survey)
    {
        $responses = $this->responseService->getAllBySurveyIdAndVersion($survey->getSurveyId(), $survey->getVersion());
        if ($responses) {
            foreach ($responses as $response) {
                $this->responseEventService->deleteEvents($response->getResponseId());
                $this->responseService->delete($response);
            }
        }

        $this->surveyEventService->deleteEvents($survey->getSurveyId(), $survey->getVersion());
        $this->surveyService->delete($survey);
        $this->surveyEventLoggerService->log(SurveyEventLogger::SURVEY_DELETED, $survey);
    }
}
