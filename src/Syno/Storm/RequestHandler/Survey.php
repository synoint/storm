<?php

namespace Syno\Storm\RequestHandler;

use Symfony\Component\HttpFoundation\Request;
use Syno\Storm\Document;
use Syno\Storm\Services;
use Syno\Storm\Traits\RouteAware;

class Survey
{
    use RouteAware;

    CONST ATTR = 'survey';

    private Services\Survey $surveyService;

    public function __construct(Services\Survey $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    public function hasSurveyId(Request $request): bool
    {
        return $request->attributes->has('surveyId');
    }

    public function getPublished(int $surveyId):? Document\Survey
    {
        return $this->surveyService->getPublished($surveyId);
    }

    public function findSavedBySurveyIdAndVersion(int $surveyId, int $versionId):? Document\Survey
    {
        return $this->surveyService->findBySurveyIdAndVersion($surveyId, $versionId);
    }

    public function setSurvey(Request $request, Document\Survey $survey)
    {
        $request->attributes->set(self::ATTR, $survey);
    }

    public function hasSurvey(Request $request): bool
    {
        return $request->attributes->has(self::ATTR);
    }

    public function getSurvey(Request $request): Document\Survey
    {
        $survey = $request->attributes->get(self::ATTR);
        if (!$survey instanceof Document\Survey) {
            throw new \UnexpectedValueException('Survey attribute is invalid');
        }

        return $survey;
    }

    public function getSurveyId(Request $request): int
    {
        return $request->attributes->getInt('surveyId');
    }

    public function getVersionId(Request $request): int
    {
        return $request->attributes->getInt(
            'versionId',
            (int) $this->surveyService->findLatestVersion($this->getSurveyId($request))
        );
    }
}
