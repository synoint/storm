<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class SurveyStats
{
    /** @var DocumentManager */
    private $dm;

    /**
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    /**
     * @param int $surveyId
     * @param int $version
     *
     * @return Document\SurveyStats
     */
    public function getNew(int $surveyId, int $version)
    {
        $surveyStats = new Document\SurveyStats();
        $surveyStats->setSurveyId($surveyId)->setVersion($version);

        return $surveyStats;
    }

    /**
     * @param Document\SurveyStats $surveyStats
     */
    public function save(Document\SurveyStats $surveyStats)
    {
        $this->dm->persist($surveyStats);
        $this->dm->flush();
    }

    /**
     * @param int $surveyId
     * @param int $version
     */
    public function delete(int $surveyId, int $version)
    {
        $this->dm->getDocumentCollection(Document\SurveyStats::class)->deleteOne(
            [
                'surveyId' => $surveyId,
                'version'  => $version
            ]
        );
    }

    /**
     * @param int $surveyId
     * @param int $version
     *
     * @return null|Document\SurveyStats
     */
    public function findBySurveyIdAndVersion(int $surveyId, int $version):? object
    {
        return $this->dm->getRepository(Document\SurveyStats::class)->findOneBy(
            [
                'surveyId' => $surveyId,
                'version' => $version,
            ]
        );
    }

    /**
     * @param int $surveyId
     * @param int $version
     */
    public function incrementVisits(int $surveyId, int $version)
    {
        $this->dm->getDocumentCollection(Document\SurveyStats::class)->updateOne(
            [
                'surveyId' => $surveyId,
                'version'  => $version
            ],
            [
                '$inc'         => ['visits' => 1],
                '$currentDate' => ['updatedAt' => true]
            ]
        );
    }

    /**
     * @param int    $surveyId
     * @param int    $version
     * @param string $mode
     */
    public function incrementResponses(int $surveyId, int $version, string $mode)
    {
        switch ($mode) {
            case Document\Response::MODE_DEBUG:
                $field = 'debugResponses';
                break;
            case Document\Response::MODE_TEST:
                $field = 'testResponses';
                break;
            case Document\Response::MODE_LIVE:
                $field = 'liveResponses';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid mode %s', $mode));
        }

        $this->dm->getDocumentCollection(Document\SurveyStats::class)->updateOne(
            [
                'surveyId' => $surveyId,
                'version'  => $version
            ],
            [
                '$inc'         => [$field => 1],
                '$currentDate' => ['updatedAt' => true]
            ]
        );
    }

    /**
     * @param int $surveyId
     * @param int $version
     */
    public function incrementCompletes(int $surveyId, int $version)
    {
        $this->dm->getDocumentCollection(Document\SurveyStats::class)->updateOne(
            [
                'surveyId' => $surveyId,
                'version'  => $version
            ],
            [
                '$inc'         => ['completes' => 1],
                '$currentDate' => ['updatedAt' => true]
            ]
        );
    }
}
