<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use Syno\Storm\Document;

class Page
{
    private DocumentManager $dm;
    private LoggerInterface $logger;

    public function __construct(DocumentManager $dm, LoggerInterface $logger)
    {
        $this->dm = $dm;
        $this->logger = $logger;
    }

    public function save(Document\Page $page)
    {
        $this->dm->persist($page);
        $this->dm->flush();
    }

    public function findBySurvey(Document\Survey $survey): Collection
    {
        $s = microtime(true);
        $pages = $this->dm->getRepository(Document\Page::class)->findBy(
            [
                'surveyId' => $survey->getSurveyId(),
                'version'  => $survey->getVersion(),
            ]
        );
        $this->logger->error(
            '{surveyId: ' . $survey->getSurveyId(). ', version: ' . $survey->getVersion().'}, '.
            round(microtime(true) - $s, 4)
        );

        return new ArrayCollection($pages);
    }

    public function deleteBySurvey(Document\Survey $survey)
    {
        $pages = $this->dm->getRepository(Document\Page::class)->findBy(
            [
                'surveyId' => $survey->getSurveyId(),
                'version'  => $survey->getVersion(),
            ]
        );

        foreach ($pages as $page) {
            $this->dm->remove($page);
            $this->dm->flush();
        }
    }
}
