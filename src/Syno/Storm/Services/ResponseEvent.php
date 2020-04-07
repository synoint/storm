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
     * @return object|Document\ResponseEvent
     */
    public function getLastSavedAnswer(int $surveyId)
    {
        return $this->dm
            ->createQueryBuilder(Document\ResponseEvent::class)
            ->field('surveyId')->equals($surveyId)
            ->field('message')->equals(ResponseEventLogger::ANSWERS_SAVED)
            ->sort('time', 'DESC')
            ->getQuery()
            ->getSingleResult();
    }
}
