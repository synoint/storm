<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class Response
{
    const MODE_LIVE  = 'live';
    const MODE_TEST  = 'test';
    const MODE_DEBUG = 'debug';

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
     * @param string $route
     *
     * @return string
     */
    public function getMode(string $route)
    {
        switch ($route) {
            case 'survey.index':
                return self::MODE_LIVE;
            case 'survey.test':
                return self::MODE_TEST;
            case 'survey.debug':
                return self::MODE_DEBUG;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown route: "%s"', $route));
        }
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
