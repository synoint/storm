<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document\Response;
use Syno\Storm\Document\ResponseEvent;

class ResponseEventLogger
{
    const RESPONSE_CREATED = 'created';
    const RESPONSE_CLEARED = 'cleared';

    const SURVEY_ENTERED             = 'survey entered';
    const SURVEY_RESUMED             = 'survey resumed';
    const SURVEY_COMPLETED           = 'survey completed';
    const SURVEY_VERSION_UNAVAILABLE = 'survey version unavailable';

    const PAGE_ENTERED      = 'page entered';
    const ANSWERS_SUBMITTED = 'answers submitted';
    const ANSWERS_SAVED     = 'answers saved';
    const ANSWERS_ERROR     = 'answers error';

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
     * @param string   $event
     * @param Response $response
     */
    public function log(string $event, Response $response)
    {
        if (!$response->isLive()) {
            return;
        }

        switch ($event) {
            case self::RESPONSE_CREATED:
            case self::RESPONSE_CLEARED:
            case self::SURVEY_ENTERED:
            case self::SURVEY_COMPLETED:
            case self::SURVEY_VERSION_UNAVAILABLE:
                $document = new ResponseEvent($event, $response->getResponseId(), $response->getSurveyId());
                break;
            case self::SURVEY_RESUMED:
            case self::PAGE_ENTERED:
                $document = new ResponseEvent(
                    $event,
                    $response->getResponseId(),
                    $response->getSurveyId(),
                    $response->getPageId()
                );
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown response event "%s"', $event));
        }

        $this->dm->persist($document);
        $this->dm->flush();
    }
}
