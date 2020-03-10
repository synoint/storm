<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document\Survey;
use Syno\Storm\Document\SurveyEvent;

class SurveyEventLogger
{
    const VISIT          = 'visit';
    const DEBUG_RESPONSE = 'debug_response';
    const TEST_RESPONSE  = 'test_response';
    const LIVE_RESPONSE  = 'live_response';
    const COMPLETE       = 'complete';
    const SCREENOUT      = 'screenout';

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
     * @param string $event
     * @param Survey $survey
     */
    public function log(string $event, Survey $survey)
    {
        $document = new SurveyEvent($survey->getSurveyId(), $survey->getVersion(), $event);

        $this->dm->persist($document);
        $this->dm->flush();
    }
}
