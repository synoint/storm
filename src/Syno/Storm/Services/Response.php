<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class Response
{
    /** @var DocumentManager */
    private $dm;

    /**
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function getNew(Document\Survey $survey, string $mode, string $locale, string $uid = null)
    {
        if (empty($uid)) {
            $uid = $this->generateUid();
        }

        $result = new Document\Response();
        $result
            ->setUid($uid)
            ->setSurveyId($survey->getSurveyId())
            ->setSurveyVersion($survey->getVersion())
            ->setMode($mode)
            ->setLocale($locale);

        return $result;
    }

    /**
     * @return string
     */
    protected function generateUid(): string
    {
        return bin2hex(random_bytes(7)) . uniqid();
    }


}
