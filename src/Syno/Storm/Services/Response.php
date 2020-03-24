<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class Response
{
    /** @var DocumentManager */
    private $dm;
    /** @var string */
    private $responseIdPrefix;

    /**
     * @param DocumentManager $documentManager
     * @param string          $responseIdPrefix
     */
    public function __construct(DocumentManager $documentManager, string $responseIdPrefix)
    {
        $this->dm               = $documentManager;
        $this->responseIdPrefix = $responseIdPrefix;
    }

    /**
     * @param int    $surveyId
     * @param string $responseId
     *
     * @return null|Document\Response
     */
    public function findBySurveyIdAndResponseId(int $surveyId, string $responseId):? object
    {
        return $this->dm->getRepository(Document\Response::class)->findOneBy(
            [
                'surveyId'   => $surveyId,
                'responseId' => $responseId
            ]
        );
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
        return $this->dm->getRepository(Document\Response::class)->findBy(
            [
                'surveyId' => $surveyId
            ],
            [
                'id' => 'DESC'
            ],
            $limit,
            $offset
        );
    }

    /**
     * @param int $surveyId
     *
     * @return int
     */
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

    /**
     * @param string|null $responseId
     *
     * @return Document\Response
     */
    public function getNew(string $responseId = null): Document\Response
    {
        if (empty($responseId)) {
            $responseId = $this->generateResponseId();
        }

        return new Document\Response($responseId);
    }

    /**
     * @param Document\Response $response
     */
    public function save(Document\Response $response)
    {
        $this->dm->persist($response);
        $this->dm->flush();
    }

    /**
     * @return string
     */
    private function generateResponseId(): string
    {
        return uniqid($this->responseIdPrefix);
    }
}
