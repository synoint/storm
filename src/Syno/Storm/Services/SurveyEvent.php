<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class SurveyEvent
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
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getAllBySurveyId(int $surveyId, int $limit = 1000, int $offset = 0): array
    {
        return $this->dm->getRepository(Document\SurveyEvent::class)->findBy(
            [
                'surveyId' => $surveyId
            ],
            [
                'id' => 'ASC'
            ],
            $limit,
            $offset
        );
    }

    /**
     * @param int    $surveyId
     * @param int    $version
     * @param string $event
     *
     * @return int
     */
    public function count(int $surveyId, int $version = null, string $event = null): int
    {
        $qb = $this->dm->createQueryBuilder(Document\SurveyEvent::class)->field('surveyId')->equals($surveyId);

        if (null !== $version) {
            $qb->field('version')->equals($version);
        }

        if (null !== $event) {
            $qb->field('event')->equals($event);
        }

        return $qb->count()->getQuery()->execute();
    }

    /**
     * @param int $surveyId
     *
     * @return array
     */
    public function getAvailableVersions(int $surveyId)
    {
        return $this->dm->createQueryBuilder(Document\SurveyEvent::class)
            ->select('version')
            ->distinct('version')
            ->field('surveyId')->equals($surveyId)
            ->getQuery()
            ->execute();

    }
}
