<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document\Response;
use Syno\Storm\Document\ResponseEvent;

class ResponseEventLogger
{
    const RESPONSE_CREATED = 'created';
    const RESPONSE_CLEARED = 'cleared';
    const SURVEY_ENTERED = 'survey entered';

    const SURVEY_RESUMED = 'survey resumed';
    const SURVEY_COMPLETED = 'survey completed';
    const SURVEY_VERSION_UNAVAILABLE = 'survey version unavailable';

    const QUALITY_SCREENOUT = 'quality screenout';
    const QUALITY_SCREENOUT_CLEARED = 'quality screenout cleared';

    const SURVEY_MODE_CHANGED = 'survey mode changed';

    const SURVEY_SCREENOUTED = 'survey screenouted';
    const SURVEY_QUALITY_SCREENOUTED = 'survey quality screenouted';

    const PAGE_ENTERED = 'page entered';
    const ANSWERS_SAVED = 'answers saved';
    const ANSWERS_CLEARED = 'answers cleared';
    const ANSWERS_ERROR = 'answers error';

    const JUMPED_TO_END_OF_SURVEY = 'jumped to end';
    const JUMPED_TO_PAGE = 'jumped to page';

    private DocumentManager $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function log(string $event, Response $response)
    {
        if (!$response->isLive() && !$response->isTest()) {
            return;
        }

        switch ($event) {
            case self::RESPONSE_CREATED:
            case self::RESPONSE_CLEARED:
            case self::QUALITY_SCREENOUT:
            case self::QUALITY_SCREENOUT_CLEARED:
            case self::SURVEY_ENTERED:
            case self::SURVEY_COMPLETED:
            case self::SURVEY_SCREENOUTED:
            case self::SURVEY_QUALITY_SCREENOUTED:
            case self::SURVEY_VERSION_UNAVAILABLE:
            case self::SURVEY_MODE_CHANGED:
            case self::JUMPED_TO_END_OF_SURVEY:
                $document = new ResponseEvent($event, $response->getResponseId(), $response->getSurveyId());
                break;
            case self::SURVEY_RESUMED:
            case self::PAGE_ENTERED:
            case self::ANSWERS_SAVED:
            case self::ANSWERS_CLEARED:
            case self::ANSWERS_ERROR:
            case self::JUMPED_TO_PAGE:
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

//        $this->dm->clear();
        $this->dm->persist($document);
        $this->dm->flush();
    }
}
