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

    public function logResponse(Document\Response $response, Document\Survey $survey)
    {
        switch ($response->getMode()) {
            case Document\Response::MODE_LIVE:
                $this->log(SurveyEventLogger::LIVE_RESPONSE, $survey);
                break;
            case Document\Response::MODE_TEST:
                $this->log(SurveyEventLogger::TEST_RESPONSE, $survey);
                break;
            case Document\Response::MODE_DEBUG:
                $this->log(SurveyEventLogger::DEBUG_RESPONSE, $survey);
                break;
        }
    }

    public function logComplete(Document\Response $response, Document\Survey $survey)
    {
        switch ($response->getMode()) {
            case Document\Response::MODE_LIVE:
                $this->log(SurveyEventLogger::LIVE_COMPLETE, $survey);
                break;
            case Document\Response::MODE_TEST:
                $this->log(SurveyEventLogger::TEST_COMPLETE, $survey);
                break;
            case Document\Response::MODE_DEBUG:
                $this->log(SurveyEventLogger::DEBUG_COMPLETE, $survey);
                break;
        }
    }
}
