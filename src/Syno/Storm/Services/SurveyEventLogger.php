<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;
use Syno\Storm\Document\SurveyEvent;

class SurveyEventLogger
{
    public const SURVEY_CREATED     = 'created';
    public const SURVEY_PUBLISHED   = 'published';
    public const SURVEY_UNPUBLISHED = 'unpublished';
    public const SURVEY_DELETED     = 'deleted';
    public const LIVE_VISIT         = 'live_visit';

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
