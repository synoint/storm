<?php

namespace Syno\Storm\RequestHandler;

use Symfony\Component\HttpFoundation\RequestStack;
use Syno\Storm\Document;
use Syno\Storm\Services;
use Syno\Storm\Traits\RouteAware;

class Survey
{
    use RouteAware;

    const ATTR = 'survey';

    private RequestStack    $requestStack;
    private Services\Survey $surveyService;
    private Services\Page   $pageService;

    public function __construct(RequestStack $requestStack, Services\Survey $surveyService, Services\Page $pageService)
    {
        $this->requestStack  = $requestStack;
        $this->surveyService = $surveyService;
        $this->pageService   = $pageService;
    }

    public function getSurvey(): Document\Survey
    {
        $survey = $this->requestStack->getCurrentRequest()->attributes->get(self::ATTR);
        if (!$survey instanceof Document\Survey) {
            throw new \UnexpectedValueException('Survey attribute is invalid');
        }

        return $survey;
    }

    public function setSurvey(Document\Survey $survey)
    {
        $survey->setPages($this->pageService->findBySurvey($survey));
        $this->requestStack->getCurrentRequest()->attributes->set(self::ATTR, $survey);
    }

    public function hasSurvey(): bool
    {
        return $this->requestStack->getCurrentRequest()->attributes->has(self::ATTR);
    }

    public function hasId(): bool
    {
        return $this->requestStack->getCurrentRequest()->attributes->has('surveyId');
    }

    public function getId(): int
    {
        return $this->requestStack->getCurrentRequest()->attributes->getInt('surveyId');
    }

    public function getPublished(int $surveyId): ?Document\Survey
    {
        $survey = $this->surveyService->getPublished($surveyId);

        if ($survey) {
            $this->surveyService->detachSurvey($survey);
        }

        return $survey;
    }

    public function findSaved(int $surveyId, int $versionId): ?Document\Survey
    {
        return $this->surveyService->findBySurveyIdAndVersion($surveyId, $versionId);
    }

    public function getVersionId(): int
    {
        return $this->requestStack->getCurrentRequest()->attributes->getInt(
            'versionId',
            (int)$this->surveyService->findLatestVersion($this->getId())
        );
    }
}
