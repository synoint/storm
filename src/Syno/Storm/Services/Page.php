<?php

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class Page
{
    private DocumentManager        $dm;
    private TagAwareCacheInterface $cache;

    private array $pageIdCache = [];

    public function __construct(DocumentManager $dm, TagAwareCacheInterface $surveyCache)
    {
        $this->dm    = $dm;
        $this->cache = $surveyCache;
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
        $pages  = $this->dm->createQueryBuilder(Document\Page::class)
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
        $key = 'survey_page_ids_' . $surveyId . '_' . $version;
        if (!isset($this->pageIdCache[$key])) {
            $result = $this->cache->get($key, function (ItemInterface $item) use ($surveyId, $version) {
                $item->expiresAfter(300);

                $pages = $this->dm->createQueryBuilder(Document\Page::class)
                    ->hydrate(false)
                    ->select('pageId')
                    ->field('surveyId')->equals($surveyId)
                    ->field('version')->equals($version)
                    ->sort('sortOrder')
                    ->getQuery()
                    ->execute();

                $pageIds = [];
                foreach ($pages as $page) {
                    $pageIds[] = $page['pageId'];
                }

                return $pageIds;
            });

            if ($result) {
                $this->pageIdCache[$key] = $result;
            }
        }

        return $this->pageIdCache[$key] ?? [];
    }

    public function findFirstPageId(int $surveyId, int $version): ?int
    {
        $result  = null;
        $pageIds = $this->findPageIds($surveyId, $version);
        if ($pageIds) {
            $result = $pageIds[0];
        }

        return $result;
    }

    public function findNextPageId(int $surveyId, int $version, int $currentPageId): ?int
    {
        $pageIds = $this->findPageIds($surveyId, $version);
        $result  = null;
        if ($pageIds) {
            $pick = false;
            foreach ($pageIds as $pageId) {
                if ($pick) {
                    $result = $pageId;
                    break;
                }
                $pick = ($pageId === $currentPageId);
            }
        }

        return $result;
    }

    public function findLastPageId(int $surveyId, int $version): ?int
    {
        $result  = null;
        $pageIds = $this->findPageIds($surveyId, $version);
        if ($pageIds) {
            $result = $pageIds[count($pageIds) - 1];
        }

        return $result;
    }

    public function findPage(int $surveyId, int $version, int $pageId): ?Document\Page
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

    public function findPageIdByQuestionId(int $surveyId, int $version, int $questionId): ?int
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

    public function getTotalQuestions(int $surveyId, int $version)
    {
        $builder = $this->dm->createAggregationBuilder(Document\Page::class);
        $builder
            ->hydrate(false)
            ->match()
                ->field('surveyId')->equals($surveyId)
                ->field('version')->equals($version)
                ->field('questions.hidden')->equals(false)
            ->unwind('$questions')
            ->group()
                ->field('id')->expression('$questions')
                ->count('questions');

        $total = $builder->getAggregation()->getIterator()->toArray();

        return count($total) ? $total[0]['questions'] : 0;
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
