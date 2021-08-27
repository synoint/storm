<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class SurveyEvent
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

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

    public function getAll(string $idOffset = '', int $limit = 1000)
    {
        $qb = $this->dm->createQueryBuilder(Document\SurveyEvent::class);
        if ($idOffset) {
            $qb->field('id')->gt($idOffset);
        }
        $qb->sort('id')->limit($limit);

        return $qb->getQuery()->execute();
    }

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

    public function getAvailableVersions(int $surveyId): array
    {
        return $this->dm->createQueryBuilder(Document\SurveyEvent::class)
            ->select('version')
            ->distinct('version')
            ->field('surveyId')->equals($surveyId)
            ->getQuery()
            ->execute();
    }

    public function deleteEvents(int $surveyId, int $version)
    {
        $this->dm->createQueryBuilder(Document\SurveyEvent::class)
            ->remove()
            ->field('surveyId')->equals($surveyId)
            ->field('version')->equals($version)
            ->getQuery()
            ->execute();
    }
}
