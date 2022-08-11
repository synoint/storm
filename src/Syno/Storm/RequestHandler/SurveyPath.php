<?php

namespace Syno\Storm\RequestHandler;

use Symfony\Component\HttpFoundation\RequestStack;
use Syno\Storm\Document;
use Syno\Storm\Services;
use Syno\Storm\Traits\RouteAware;

class SurveyPath
{
    use RouteAware;

    const ATTR = 'surveyPath';

    private RequestStack        $requestStack;
    private Services\Survey     $surveyService;
    private Services\SurveyPath $surveyPathService;

    public function __construct(
        RequestStack $requestStack,
        Services\Survey $surveyService,
        Services\SurveyPath $surveyPathService
    ) {
        $this->requestStack      = $requestStack;
        $this->surveyService     = $surveyService;
        $this->surveyPathService = $surveyPathService;
    }

    public function getSurveyPath(): Document\SurveyPath
    {
        $surveyPath = $this->requestStack->getCurrentRequest()->attributes->get(self::ATTR);
        if (!$surveyPath instanceof Document\SurveyPath) {
            throw new \UnexpectedValueException('Survey path attribute is invalid');
        }

        return $surveyPath;
    }

    public function setSurveyPath(Document\SurveyPath $surveyPath)
    {
        $this->requestStack->getCurrentRequest()->attributes->set(self::ATTR, $surveyPath);
    }

    public function hasSurveyPath(): bool
    {
        return $this->requestStack->getCurrentRequest()->attributes->has(self::ATTR);
    }

    public function findSurveyPath(Document\Survey $survey): ?Document\SurveyPath
    {
        $paths = $this->surveyPathService->find($survey);

        if (count($paths)) {
            return $this->surveyPathService->getRandomWeightedElement($paths);
        }

        return null;
    }
}
