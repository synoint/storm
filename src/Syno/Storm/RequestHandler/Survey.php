<?php

namespace Syno\Storm\RequestHandler;

use Symfony\Component\HttpFoundation\Request;
use Syno\Storm\Document;
use Syno\Storm\Services;

class Survey
{
    CONST ATTR = 'survey';

    /** @var Services\Survey */
    private $surveyService;

    /**
     * @param Services\Survey $surveyService
     */
    public function __construct(Services\Survey $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function hasSurveyId(Request $request)
    {
        return $request->attributes->has('surveyId');
    }

    /**
     * @param Request $request
     *
     * @return Document\Survey|null
     */
    public function fetchSurvey(Request $request)
    {
        $survey = null;
        $surveyId = $request->attributes->getInt('surveyId');
        if ($surveyId) {
            if ($this->isDebugRoute($request)) {
                $survey = $this->findSavedBySurveyIdAndVersion($surveyId, $this->getVersionId($request));
            } else {
                $survey = $this->surveyService->getPublished($surveyId);
            }
        }

        return $survey;
    }

    /**
     * @param int $surveyId
     * @param int $versionId
     *
     * @return Document\Survey|null
     */
    public function findSavedBySurveyIdAndVersion(int $surveyId, int $versionId)
    {
        return $this->surveyService->findBySurveyIdAndVersion($surveyId, $versionId);
    }

    /**
     * @param Request         $request
     * @param Document\Survey $survey
     */
    public function setSurvey(Request $request, Document\Survey $survey)
    {
        $request->attributes->set(self::ATTR, $survey);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function hasSurvey(Request $request)
    {
        return $request->attributes->has(self::ATTR);
    }

    /**
     * @param Request $request
     *
     * @return Document\Survey
     */
    public function getSurvey(Request $request)
    {
        $survey = $request->attributes->get(self::ATTR);
        if (!$survey instanceof Document\Survey) {
            throw new \UnexpectedValueException('Survey attribute is invalid');
        }

        return $survey;
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    protected function getSurveyId(Request $request)
    {
        return $request->attributes->getInt('surveyId');
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    protected function getVersionId(Request $request)
    {
        return $request->attributes->getInt(
            'versionId',
            (int) $this->surveyService->findLatestVersion($this->getSurveyId($request))
        );
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isDebugRoute(Request $request)
    {
        return 'survey.debug' === $request->attributes->get('_route');
    }
}
