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

    public function getSurveyCompletesMap(int $surveyId): array
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

    public function getResponseCompleteTime(string $responseId): ?int
    {
        $event = $this->dm->getRepository(Document\ResponseEvent::class)->findOneBy(
            [
                'responseId' => $responseId,
                'message'    => ResponseEventLogger::SURVEY_COMPLETED,
            ]
        );

        return $event ? $event->getTimestamp() : null;
    }

    /**
     * @return Document\ResponseEvent[]
     */
    public function getResponseEvents(string $responseId): array
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

    public function deleteEvents(int $responseId)
    {
        $this->dm->createQueryBuilder(Document\ResponseEvent::class)
            ->remove()
            ->field('responseId')->equals($responseId)
            ->getQuery()
            ->execute();
    }
}
