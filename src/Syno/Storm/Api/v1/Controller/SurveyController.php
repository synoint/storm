<?php

namespace Syno\Storm\Api\v1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Syno\Storm\Api\Controller\TokenAuthenticatedController;
use Syno\Storm\Api\v1\Form;
use Syno\Storm\Api\v1\Http\ApiResponse;
use Syno\Storm\Document;
use Syno\Storm\Services\Response;
use Syno\Storm\Services\ResponseEvent;
use Syno\Storm\Services\Survey;
use Syno\Storm\Services\SurveyEventLogger;
use Syno\Storm\Services\SurveyEvent;
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

    /** @var SurveyEvent */
    private $surveyEventService;

    /** @var Response */
    private $responseService;

    /** @var ResponseEvent */
    private $responseEventService;

    /**
     * @param Survey        $surveyService
     * @param SurveyEvent   $surveyEventService
     * @param Response      $responseService
     * @param ResponseEvent $responseEventService
     */
    public function __construct(
        Survey $surveyService,
        SurveyEvent $surveyEventService,
        Response $responseService,
        ResponseEvent $responseEventService
    )
    {
        $this->surveyService        = $surveyService;
        $this->surveyEventService   = $surveyEventService;
        $this->responseService      = $responseService;
        $this->responseEventService = $responseEventService;
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
     *     requirements={"id"="\d+", "version"="\d+"},
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

        $this->surveyEventService->removeEvents($surveyId, $version);
        $this->surveyService->delete($survey);

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
     * @param int $surveyId
     * @param int $version
     *
     * @Route(
     *     "/{surveyId}/{version}/unpublish",
     *     name="storm_api.v1.survey.unpublish",
     *     requirements={"id"="\d+", "version"="\d+"},
     *     methods={"PUT"}
     * )
     *
     * @return JsonResponse
     */
    public function unpublish(int $surveyId, int $version)
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
     * @param int     $surveyId
     * @param Request $request
     *
     * @Route(
     *     "/{surveyId}/events",
     *     name="storm_api.v1.survey.events",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return JsonResponse
     */
    public function events(int $surveyId, Request $request)
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
     * @param int $surveyId
     *
     * @Route(
     *     "/{surveyId}/events/summary",
     *     name="storm_api.v1.survey.event_count",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return JsonResponse
     */
    public function eventSummary(int $surveyId)
    {
        $result = [];
        foreach ($this->surveyEventService->getAvailableVersions($surveyId) as $version) {
            $result[] = [
                'version'            => $version,
                'total'              => $this->surveyEventService->count($surveyId, $version),
                'visits'             => $this->surveyEventService->count($surveyId, $version, SurveyEventLogger::VISIT),
                'debug_responses'    => $this->surveyEventService->count($surveyId, $version, SurveyEventLogger::DEBUG_RESPONSE),
                'test_responses'     => $this->surveyEventService->count($surveyId, $version, SurveyEventLogger::TEST_RESPONSE),
                'live_responses'     => $this->surveyEventService->count($surveyId, $version, SurveyEventLogger::LIVE_RESPONSE),
                'screenouts'         => $this->surveyEventService->count($surveyId, $version, SurveyEventLogger::SCREENOUT),
                'quality_screenouts' => $this->surveyEventService->count($surveyId, $version, SurveyEventLogger::QUALITY_SCREENOUT),
                'completes'          => $this->surveyEventService->count($surveyId, $version, SurveyEventLogger::COMPLETE)
            ];
        }

        return $this->json($result);
    }

    /**
     * @param int $surveyId
     *
     * @Route(
     *     "/{surveyId}/events/last/date",
     *     name="storm_api.v1.survey.event.last.date",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return JsonResponse
     */
    public function eventLastDate(int $surveyId)
    {
        return $this->json($this->responseEventService->getLastDate($surveyId));
    }

    /**
     * @param int     $surveyId
     * @param Request $request
     *
     * @Route(
     *     "/{surveyId}/responses",
     *     name="storm_api.v1.survey.responses",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return JsonResponse
     */
    public function responses(int $surveyId, Request $request)
    {
        $total = $this->responseService->count($surveyId);
        $limit = $request->query->getInt('limit', 1000000);
        $limit = max($limit, 1);

        $responses = [];
        if ($total) {
            $responses = $this->responseService->getAllBySurveyId($surveyId, $limit);
            if ($responses) {
                $completesMap = $this->responseEventService->getSurveyCompletesMap($surveyId);
                /** @var Document\Response $response */
                foreach ($responses as $response) {
                    if ($response->isCompleted()) {
                        $response->setCompletedAt($completesMap[$response->getResponseId()] ?? 0);
                    }
                }
            }
        }

        return $this->json(
            [
                'responses' => $responses,
                'limit'     => $limit,
                'total'     => $total
            ]
        );
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
            }
        }
    }

}
