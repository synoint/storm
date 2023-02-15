<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;
use Syno\Storm\Document\SurveyEvent;

class SurveyEventLogger
{
    public const LIVE_VISIT = 'live_visit';

    public const LIVE_RESPONSE  = 'live_response';
    public const TEST_RESPONSE  = 'test_response';
    public const DEBUG_RESPONSE = 'debug_response';

    public const LIVE_COMPLETE  = 'live_complete';
    public const TEST_COMPLETE  = 'test_complete';
    public const DEBUG_COMPLETE = 'debug_complete';

    public const LIVE_SCREENOUT  = 'live_screenout';
    public const TEST_SCREENOUT  = 'test_screenout';
    public const DEBUG_SCREENOUT = 'debug_screenout';

    public const LIVE_QUALITY_SCREENOUT  = 'live_quality_screenout';
    public const TEST_QUALITY_SCREENOUT  = 'test_quality_screenout';
    public const DEBUG_QUALITY_SCREENOUT = 'debug_quality_screenout';

    public const SURVEY_CREATED     = 'created';
    public const SURVEY_PUBLISHED   = 'published';
    public const SURVEY_UNPUBLISHED = 'unpublished';
    public const SURVEY_DELETED     = 'deleted';

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

    public function logScreenout(Document\Response $response, Document\Survey $survey)
    {
        switch ($response->getMode()) {
            case Document\Response::MODE_LIVE:
                $this->log(SurveyEventLogger::LIVE_SCREENOUT, $survey);
                break;
            case Document\Response::MODE_TEST:
                $this->log(SurveyEventLogger::TEST_SCREENOUT, $survey);
                break;
            case Document\Response::MODE_DEBUG:
                $this->log(SurveyEventLogger::DEBUG_SCREENOUT, $survey);
                break;
        }
    }

    public function logQualityScreenout(Document\Response $response, Document\Survey $survey)
    {
        switch ($response->getMode()) {
            case Document\Response::MODE_LIVE:
                $this->log(SurveyEventLogger::LIVE_QUALITY_SCREENOUT, $survey);
                break;
            case Document\Response::MODE_TEST:
                $this->log(SurveyEventLogger::TEST_QUALITY_SCREENOUT, $survey);
                break;
            case Document\Response::MODE_DEBUG:
                $this->log(SurveyEventLogger::DEBUG_QUALITY_SCREENOUT, $survey);
                break;
        }
    }
}
