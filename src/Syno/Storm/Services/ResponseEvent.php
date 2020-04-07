<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class ResponseEvent
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
     *
     * @return array
     */
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
            $result[$event->getResponseId()] = $event->getTime()->getTimestamp();
        }

        return $result;
    }

    /**
     * @param int $surveyId
     *
     * @return null|object
     * @throws \Exception
     */
    public function getLastEventDate(int $surveyId)
    {
        $response = $this->dm->getDocumentCollection(Document\ResponseEvent::class)->findOne(
            ['surveyId' => $surveyId],
            [
                'projection' => ['time' => 1, '_id' => 0],
                'sort'       => ['time' => -1],
            ]);
        return !empty($response) ? $response['time']->toDateTime() : null;
    }
}
