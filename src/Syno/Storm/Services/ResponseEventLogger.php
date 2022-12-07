<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document\Response;
use Syno\Storm\Document\ResponseEvent;

class ResponseEventLogger
{
    const RESPONSE_CREATED = 'created';
    const RESPONSE_CLEARED = 'cleared';
    const SURVEY_ENTERED   = 'survey entered';

    const SURVEY_RESUMED             = 'survey resumed';
    const SURVEY_COMPLETED           = 'survey completed';
    const SURVEY_VERSION_UNAVAILABLE = 'survey version unavailable';

    const RESPONSE_REMOVE            = 'removed';
    const RESPONSE_COMPLETE          = 'updated to complete';
    const RESPONSE_SCREENOUT         = 'updated to screenout';
    const RESPONSE_QUALITY_SCREENOUT = 'updated to quality screenout';
    const RESPONSE_QUOTA_FULL        = 'updated to quota full';

    const SURVEY_MODE_CHANGED = 'survey mode changed';

    const SURVEY_SCREENOUTED_ON_COMPLETE_CONDITION  = 'response screenout on survey complete condition';
    const SURVEY_SCREENOUTED_ON_SCREENOUT_CONDITION = 'response screenout on survey screenout condition';

    const SURVEY_SCREENOUTED         = 'survey screenouted';
    const SURVEY_QUALITY_SCREENOUTED = 'survey quality screenouted';

    const PAGE_ENTERED    = 'page entered';
    const ANSWERS_SAVED   = 'answers saved';
    const ANSWERS_CLEARED = 'answers cleared';
    const ANSWERS_ERROR   = 'answers error';

    const JUMPED_TO_END_OF_SURVEY = 'jumped to end';
    const JUMPED_TO_PAGE          = 'jumped to page';

    private DocumentManager $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function log(string $event, Response $response, Collection $answers = null)
    {
        if (!$response->isLive() && !$response->isTest()) {
            return;
        }

        switch ($event) {
            case self::RESPONSE_CREATED:
            case self::RESPONSE_CLEARED:
            case self::SURVEY_ENTERED:
            case self::SURVEY_COMPLETED:
            case self::SURVEY_SCREENOUTED_ON_COMPLETE_CONDITION:
            case self::SURVEY_SCREENOUTED_ON_SCREENOUT_CONDITION:
            case self::SURVEY_SCREENOUTED:
            case self::SURVEY_QUALITY_SCREENOUTED:
            case self::SURVEY_VERSION_UNAVAILABLE:
            case self::SURVEY_MODE_CHANGED:
            case self::JUMPED_TO_END_OF_SURVEY:
            case self::RESPONSE_REMOVE:
            case self::RESPONSE_COMPLETE:
            case self::RESPONSE_SCREENOUT:
            case self::RESPONSE_QUALITY_SCREENOUT:
            case self::RESPONSE_QUOTA_FULL:
                $document = new ResponseEvent($event, $response->getResponseId(), $response->getSurveyId());
                break;
            case self::SURVEY_RESUMED:
            case self::PAGE_ENTERED:
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
            case self::ANSWERS_SAVED:
                $document = new ResponseEvent(
                    $event,
                    $response->getResponseId(),
                    $response->getSurveyId(),
                    $response->getPageId(),
                    $answers
                );
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown response event "%s"', $event));
        }

        $this->dm->persist($document);
        $this->dm->flush();
    }
}
