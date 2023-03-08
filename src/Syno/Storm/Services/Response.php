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
        return $this->dm
            ->createQueryBuilder(Document\Response::class)
            ->field('surveyId')->equals($surveyId)
            ->field('responseId')->equals($responseId)
            ->field('userAgents')->notEqual(null)
            ->getQuery()
            ->getSingleResult();
    }

    public function getAllBySurveyId(int $surveyId, int $limit = 1000, int $offset = 0, array $params = []):array
    {
        $qb = $this->dm
            ->createQueryBuilder(Document\Response::class)
            ->field('surveyId')->equals($surveyId);

        if (isset($params['mode'])) {
            $qb->field('mode')->equals($params['mode']);
        }

        if (isset($params['completed'])) {
            $qb->field('completed')->equals($params['completed']);
        }

        return $qb->sort('id', 'DESC')
            ->field('userAgents')->notEqual(null)
            ->limit($limit)
            ->skip($offset)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * @return Document\Response[]
     */
    public function getAllBySurveyIdAndVersion(int $surveyId, int $version): array
    {
        return $this->dm
            ->createQueryBuilder(Document\Response::class)
            ->field('surveyId')->equals($surveyId)
            ->field('surveyVersion')->equals($version)
            ->field('userAgents')->notEqual(null)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function getAllByQuestionId(int $questionId, array $params = []): array
    {
        $qb = $this->dm
            ->createQueryBuilder(Document\Response::class)
            ->field('answers.questionId')->equals($questionId);

        if (isset($params['mode'])) {
            $qb->field('mode')->equals($params['mode']);
        }

        if (isset($params['completed'])) {
            $qb->field('completed')->equals($params['completed']);
        }

        return $qb
            ->field('userAgents')->notEqual(null)
            ->getQuery()
            ->execute()
            ->toArray();
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

    private function generateResponseId(): string
    {
        return uniqid($this->responseIdPrefix);
    }
}