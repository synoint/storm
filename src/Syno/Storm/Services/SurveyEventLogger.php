<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;
use Syno\Storm\Document\SurveyEvent;

class SurveyEventLogger
{
    const VISIT             = 'visit';
    const DEBUG_RESPONSE    = 'debug_response';
    const TEST_RESPONSE     = 'test_response';
    const LIVE_RESPONSE     = 'live_response';
    const DEBUG_COMPLETE    = 'debug_complete';
    const LIVE_COMPLETE     = 'live_complete';
    const TEST_COMPLETE     = 'test_complete';
    const SCREENOUT         = 'screenout';
    const QUALITY_SCREENOUT = 'quality_screenout';

    const SURVEY_CREATED     = 'created';
    const SURVEY_PUBLISHED   = 'published';
    const SURVEY_UNPUBLISHED = 'unpublished';
    const SURVEY_DELETED     = 'deleted';

    private DocumentManager $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function log(string $event, Document\Survey $survey)
    {
        $document = new SurveyEvent($survey->getSurveyId(), $survey->getVersion(), $event);

        $this->dm->persist($document);
        $this->dm->flush();
    }
}
