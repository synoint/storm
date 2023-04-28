<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class SurveyConfig
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function save(Document\SurveyConfig $surveyConfig)
    {
        $this->dm->persist($surveyConfig);
        $this->dm->flush();
    }

    public function findBySurveyIdAndKey(int $surveyId, string $key): ?Document\SurveyConfig
    {
        return $this->dm->getRepository(Document\SurveyConfig::class)->findOneBy(['surveyId' => $surveyId, 'key' => $key]);
    }

    public function delete(Document\SurveyConfig $surveyConfig)
    {
        $this->dm->remove($surveyConfig);
        $this->dm->flush();
    }
}
