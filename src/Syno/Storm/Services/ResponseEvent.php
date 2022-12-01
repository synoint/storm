<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class ResponseEvent
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function getAll(string $idOffset = '', int $limit = 1000)
    {
        $qb = $this->dm->createQueryBuilder(Document\ResponseEvent::class);
        if ($idOffset) {
            $qb->field('id')->gt($idOffset);
        }
        $qb->sort('id')->limit($limit);

        return $qb->getQuery()->execute();
    }

    public function getAllBySurvey(int $managerSurveyId, string $idOffset = '')
    {
        $qb = $this->dm->createQueryBuilder(Document\ResponseEvent::class);
        if ($idOffset) {
            $qb->field('id')->gt($idOffset);
        }

        $qb->addAnd(['surveyId' => $managerSurveyId]);

        return $qb->getQuery()->execute();
    }

    public function getResponseCompletionTimeMap(int $surveyId): array
    {
        $data = $this->dm
            ->createQueryBuilder(Document\ResponseEvent::class)
            ->select(['responseId', 'time'])
            ->field('surveyId')->equals($surveyId)
            ->field('message')->equals(ResponseEventLogger::SURVEY_COMPLETED)
            ->getQuery()
            ->execute();

        $result = [];
        /** @var Document\ResponseEvent $event */
        foreach ($data as $event) {
            $result[$event->getResponseId()] = $event->getTimestamp();
        }

        return $result;
    }

    public function getResponseCompletionTime(string $responseId): ?int
    {
        $event = $this->dm->getRepository(Document\ResponseEvent::class)->findOneBy(
            [
                'responseId' => $responseId,
                'message'    => ResponseEventLogger::SURVEY_COMPLETED,
            ]
        );

        if (!$event) {
            $event = $this->dm->getRepository(Document\ResponseEvent::class)->findOneBy(
                [
                    'responseId' => $responseId,
                    'message'    => ResponseEventLogger::RESPONSE_COMPLETE,
                ]
            );
        }

        return $event ? $event->getTimestamp() : null;
    }

    /**
     * @return Document\ResponseEvent[]
     */
    public function getEventsByResponseId(string $responseId): array
    {
        return $this->dm->getRepository(Document\ResponseEvent::class)->findBy(
            [
                'responseId' => $responseId
            ]
        );
    }

    public function getLastDate(int $surveyId):? int
    {
        $response = $this->dm->getDocumentCollection(Document\ResponseEvent::class)->findOne(
            ['surveyId' => $surveyId],
            [
                'projection' => ['time' => 1, '_id' => 0],
                'sort'       => ['time' => -1],
            ]);

        return !empty($response) ? $response['time']->toDateTime()->getTimestamp() : null;
    }

    public function deleteEvents(string $responseId)
    {
        $this->dm->createQueryBuilder(Document\ResponseEvent::class)
            ->remove()
            ->field('responseId')->equals($responseId)
            ->getQuery()
            ->execute();
    }
}
