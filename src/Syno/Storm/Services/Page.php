<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class Page
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function save(Document\Page $page)
    {
        $this->dm->persist($page);
        $this->dm->flush();
    }

    public function findBySurvey(Document\Survey $survey): Collection
    {
        $pages = $this->dm->getRepository(Document\Page::class)->findBy(
            [
                'surveyId' => $survey->getSurveyId(),
                'version'  => $survey->getVersion(),
            ]
        );

        if (!$pages) {
            return $survey->getPages();
        }

        return new ArrayCollection($pages);
    }

    public function deleteBySurvey(Document\Survey $survey)
    {
        $pages = $this->findBySurvey($survey);

        foreach ($pages as $page) {
            $this->dm->remove($page);
            $this->dm->flush();
        }

    }

    public function pageExists(int $pageId, int $surveyId, int $version)
    {
        return $this->dm->createQueryBuilder(Document\Page::class)
                        ->count()
                        ->field('pageId')->equals($pageId)
                        ->field('surveyId')->equals($surveyId)
                        ->field('version')->equals($version)
                        ->getQuery()
                        ->execute();
    }
}
