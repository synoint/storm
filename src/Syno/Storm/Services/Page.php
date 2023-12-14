<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class Page
{
    private DocumentManager $dm;

    private array $pageIdCache = [];

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function save(Document\Page $page)
    {
        $this->dm->persist($page);
        $this->dm->flush();
    }

    public function findAllBySurvey(Document\Survey $survey): Collection
    {
        $pages = $this->dm->getRepository(Document\Page::class)->findBy(
            [
                'surveyId' => $survey->getSurveyId(),
                'version'  => $survey->getVersion(),
            ]
        );

        return new ArrayCollection($pages);
    }

    public function findAllForDebug(int $surveyId, int $version): array
    {
        $result = [];
        $pages = $this->dm->createQueryBuilder(Document\Page::class)
            ->select('pageId')
            ->select('code')
            ->field('surveyId')->equals($surveyId)
            ->field('version')->equals($version)
            ->sort('sortOrder')
            ->getQuery()
            ->execute();

        foreach ($pages as $page) {
            $result[] = $page;
        }

        return $result;
    }

    public function findPageIds(int $surveyId, int $version): array
    {
        $key = $surveyId . '_' . $version;
        if (!isset($this->pageIdCache[$key])) {
            $pages = $this->dm->createQueryBuilder(Document\Page::class)
                ->hydrate(false)
                ->select('pageId')
                ->field('surveyId')->equals($surveyId)
                ->field('version')->equals($version)
                ->sort('sortOrder')
                ->getQuery()
                ->execute();

            $result = [];
            foreach ($pages as $page) {
                $result[] = $page['pageId'];
            }
            if ($result) {
                $this->pageIdCache[$key] = $result;
            }
        }

        return $this->pageIdCache[$key] ?? [];
    }

    public function findFirstPageId(int $surveyId, int $version):? int
    {
        $result = $this->dm->createQueryBuilder(Document\Page::class)
            ->hydrate(false)
            ->select('pageId')
            ->field('surveyId')->equals($surveyId)
            ->field('version')->equals($version)
            ->sort('sortOrder')
            ->limit(1)
            ->getQuery()
            ->getSingleResult();

        return $result['pageId'] ?? null;
    }

    public function findNextPageId(int $surveyId, int $version, int $pageId):? int
    {
        $pages = $this->dm->createQueryBuilder(Document\Page::class)
            ->hydrate(false)
            ->select('pageId')
            ->field('surveyId')->equals($surveyId)
            ->field('version')->equals($version)
            ->sort('sortOrder')
            ->getQuery()
            ->execute();

        $result = null;
        if ($pages) {
            $pick = false;
            /** @var Document\Page $page */
            foreach ($pages as $page) {
                if ($pick) {
                    $result = $page['pageId'];
                    break;
                }
                $pick = ($page['pageId'] === $pageId);
            }
        }

        return $result;
    }

    public function findLastPageId(int $surveyId, int $version):? int
    {
        $result = $this->dm->createQueryBuilder(Document\Page::class)
            ->hydrate(false)
            ->select('pageId')
            ->field('surveyId')->equals($surveyId)
            ->field('version')->equals($version)
            ->sort('sortOrder', -1)
            ->limit(1)
            ->getQuery()
            ->getSingleResult();

        return $result['pageId'] ?? null;
    }

    public function findPage(int $surveyId, int $version, int $pageId):? Document\Page
    {
        $page = $this->dm->getRepository(Document\Page::class)->findOneBy(
            [
                'surveyId' => $surveyId,
                'version'  => $version,
                'pageId'   => $pageId
            ]
        );

        if (null !== $page && !$page->getPageId()) {
            $this->dm->refresh($page);
        }

        return $page;
    }

    public function findPageIdByQuestionId(int $surveyId, int $version, int $questionId):? int
    {
        $result = $this->dm->createQueryBuilder(Document\Page::class)
            ->hydrate(false)
            ->select('pageId')
            ->field('surveyId')->equals($surveyId)
            ->field('version')->equals($version)
            ->field('questions.questionId')->equals($questionId)
            ->sort('sortOrder', -1)
            ->limit(1)
            ->getQuery()
            ->getSingleResult();

        return $result['pageId'] ?? null;
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

    public function getTotalQuestions(int $surveyId, int $version): int
    {
        return $this->dm->createQueryBuilder(Document\Page::class)
            ->hydrate(false)
            ->select('questions.questionId')
            ->field('surveyId')->equals($surveyId)
            ->field('version')->equals($version)
            ->getQuery()
            ->execute()
            ->count();
    }

    public function getAnswersForDataLayer(int $surveyId, int $version)
    {
        return $this->dm->createQueryBuilder(Document\Page::class)
            ->hydrate(false)
            ->select('code')
            ->select('questions.code')
            ->select('questions.questionTypeId')
            ->select('questions.text')
            ->select('questions.answers.answerId')
            ->select('questions.answers.code')
            ->select('questions.answers.label')
            ->select('questions.answers.rowCode')
            ->select('questions.answers.rowLabel')
            ->select('questions.answers.columnCode')
            ->select('questions.answers.columnLabel')
            ->select('questions.answers.translations')
            ->field('surveyId')->equals($surveyId)
            ->field('version')->equals($version)
            ->getQuery()
            ->execute();
    }

    public function getSurveyQuestions(int $surveyId, int $version): Collection
    {
        $pages = $this->dm->createQueryBuilder(Document\Page::class)
            ->select('questions')
            ->field('surveyId')->equals($surveyId)
            ->field('version')->equals($version)
            ->getQuery()
            ->execute();

        $result = new ArrayCollection();
        foreach ($pages as $page) {
            foreach ($page->getQuestions() as $question) {
                $result[] = $question;
            }
        }

        return $result;
    }
}
