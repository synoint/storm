<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class Survey
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function getNew(): Document\Survey
    {
        $survey = new Document\Survey();
        $survey->setConfig(new Document\Config());

        return $survey;
    }

    public function save(Document\Survey $survey)
    {
        $this->dm->persist($survey);
        $this->dm->flush();
    }

    public function find(int $surveyId):? array
    {
        return $this->dm->getRepository(Document\Survey::class)->findBy(
            [
                'surveyId' => $surveyId
            ],
            [
                'version' => 'DESC'
            ]
        );
    }

    /**
     * @return null|Document\Survey
     */
    public function findBySurveyIdAndVersion(int $surveyId, int $version):? object
    {
        return $this->dm->getRepository(Document\Survey::class)->findOneBy(
            [
                'surveyId' => $surveyId,
                'version' => $version,
            ]
        );
    }

    public function detachSurvey($survey)
    {
        $this->dm->detach($survey);
    }

    public function findLatestVersion(int $surveyId):? int
    {
        $result = null;
        $surveys = $this->dm->getRepository(Document\Survey::class)->findBy(
            ['surveyId' => $surveyId],
            ['version' => 'DESC'],
            1
        );

        if ($surveys) {
            /** @var Document\Survey $lastSurvey */
            $latestSurvey = $surveys[0];
            $result = $latestSurvey->getVersion();
        }

        return $result;
    }

    /**
     * @return null|Document\Survey
     */
    public function getPublished(int $surveyId):? object
    {
        return $this->dm->getRepository(Document\Survey::class)->findOneBy(
            [
                'surveyId' => $surveyId,
                'published' => true
            ]
        );
    }

    public function publish(Document\Survey $survey)
    {
        $surveys = $this->dm->getRepository(Document\Survey::class)->findBy(
            ['surveyId' => $survey->getSurveyId()]
        );

        foreach ($surveys as &$savedSurvey) {
            if ($savedSurvey->getVersion() === $survey->getVersion()) {
                $savedSurvey->setPublished(true);
            } elseif ($savedSurvey->isPublished()) {
                $savedSurvey->setPublished(false);
            }
        }
        $this->dm->flush();
    }

    public function delete(Document\Survey $survey)
    {
        $this->dm->remove($survey);
        $this->dm->flush();
    }

    public function enableDebugMode(Document\Survey $survey): string
    {
        $token = bin2hex(random_bytes(rand(16,20)));
        $survey->getConfig()->debugMode = true;
        $survey->getConfig()->debugToken = $token;
        $this->dm->flush();

        return $token;
    }

    public function disableDebugMode(Document\Survey $survey)
    {
        $survey->getConfig()->debugMode = false;
        $survey->getConfig()->debugToken = null;
        $this->dm->flush();
    }

    public function getProgress(Document\Survey $survey, Document\Page $currentPage): int
    {
        $pages            = $survey->getPages();
        $pageCount        = $pages->count();
        $currentPageIndex = $pages->indexOf($currentPage);

        return round($currentPageIndex / $pageCount * 100);
    }
}
