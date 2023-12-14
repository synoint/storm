<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class Response
{
    private DocumentManager $dm;
    private string          $responseIdPrefix;

    public function __construct(DocumentManager $documentManager, string $responseIdPrefix)
    {
        $this->dm               = $documentManager;
        $this->responseIdPrefix = $responseIdPrefix;
    }

    /**
     * @return null|Document\Response
     */
    public function findBySurveyIdAndResponseId(int $surveyId, string $responseId): ?object
    {
        return $this->dm->getRepository(Document\Response::class)->findOneBy(
            [
                'surveyId'   => $surveyId,
                'responseId' => $responseId,
            ]
        );
    }

    public function getAllBySurveyId(int $surveyId, int $limit = 1000, int $offset = 0): array
    {
        return $this->dm->getRepository(Document\Response::class)->findBy(
            [
                'surveyId' => $surveyId
            ],
            [
                'id' => 'DESC',
            ],
            $limit,
            $offset
        );
    }

    /**
     * @return Document\Response[]
     */
    public function getAllBySurveyIdAndVersion(int $surveyId, int $version): array
    {
        return $this->dm->getRepository(Document\Response::class)->findBy(
            [
                'surveyId'      => $surveyId,
                'surveyVersion' => $version,
            ]
        );
    }

    public function count(int $surveyId): int
    {
        return $this->dm
            ->createQueryBuilder(Document\Response::class)
            ->field('surveyId')
            ->equals($surveyId)
            ->count()
            ->getQuery()
            ->execute();
    }

    public function getNew(string $responseId = null): Document\Response
    {
        if (empty($responseId)) {
            $responseId = $this->generateResponseId();
        }

        return new Document\Response($responseId);
    }

    public function save(Document\Response $response)
    {
        $this->dm->persist($response);
        $this->dm->flush();
    }

    public function getModeByRoute(string $route): string
    {
        switch ($route) {
            case 'survey.index':
                $mode = Document\Response::MODE_LIVE;
                break;
            case 'survey.test':
                $mode = Document\Response::MODE_TEST;
                break;
            case 'survey.debug':
                $mode = Document\Response::MODE_DEBUG;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown route: "%s"', $route));
        }

        return $mode;
    }

    public function delete(Document\Response $response)
    {
        $this->dm->remove($response);
        $this->dm->flush();
    }

    public function toArrayWithAnswerLabels(Document\Response $response, array $answers, array $events): array
    {
        return [
            'id'                 => $response->getId(),
            'responseId'         => $response->getResponseId(),
            'surveyId'           => $response->getSurveyId(),
            'surveyVersion'      => $response->getSurveyVersion(),
            'mode'               => $response->getMode(),
            'locale'             => $response->getLocale(),
            'completed'          => $response->isCompleted(),
            'completedAt'        => date("Y-m-d H:i:s", $response->getCompletedAt()),
            'screenedOut'        => $response->isScreenedOut(),
            'qualityScreenedOut' => $response->isQualityScreenedOut(),
            'quotaFull'          => $response->isQuotaFull(),
            'screenoutId'        => $response->getScreenoutId(),
            'createdAt'          => $response->getCreatedAt()->getTimestamp(),
            'userAgents'         => $response->getUserAgents(),
            'answers'            => $answers,
            'events'             => $events,
        ];
    }

    public function fetchLiveCount(int $surveyId, int $version): int
    {
        return $this->dm
            ->createQueryBuilder(Document\Response::class)
            ->field('surveyId')->equals($surveyId)
            ->field('surveyVersion')->equals($version)
            ->field('mode')->equals(Document\Response::MODE_LIVE)
            ->count()
            ->getQuery()
            ->execute();
    }

    private function generateResponseId(): string
    {
        return uniqid($this->responseIdPrefix);
    }
}
