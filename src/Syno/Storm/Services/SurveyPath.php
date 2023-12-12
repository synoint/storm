<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class SurveyPath
{
    private DocumentManager $dm;
    private Page            $pageService;

    private array $cache = [];

    public function __construct(DocumentManager $documentManager, Page $pageService)
    {
        $this->dm          = $documentManager;
        $this->pageService = $pageService;
    }

    public function getNew(): Document\SurveyPath
    {
        $surveyPath = new Document\SurveyPath();
        $surveyPath->setSurveyPathId(bin2hex(random_bytes(8)));

        return $surveyPath;
    }

    /**
     * @return Document\SurveyPath|null
     */
    public function findOneById(string $surveyPathId):? object
    {
        if (!isset($this->cache[$surveyPathId])) {
            $path = $this->dm->getRepository(Document\SurveyPath::class)->findOneBy(
                [
                    'surveyPathId' => $surveyPathId
                ]
            );
            if ($path) {
                $this->cache[$surveyPathId] = $path;
            }
        }

        return $this->cache[$surveyPathId] ?? null;
    }

    public function save(Document\Survey $survey, array $randomizedCombinations)
    {
        $surveyPages = $this->pageService->findAllBySurvey($survey);

        foreach ($randomizedCombinations['paths'] as $index => $combination) {
            $surveyPath = $this->getNew();
            $surveyPath->setSurveyId($survey->getSurveyId());
            $surveyPath->setVersion($survey->getVersion());

            $surveyPathPages  = new ArrayCollection();
            $pagePathCodeList = [];

            foreach ($combination as $pageId) {
                $surveyPathPage = new Document\SurveyPathPage();
                $surveyPathPage->setPageId($pageId);
                $surveyPathPages->add($surveyPathPage);

                foreach ($surveyPages as $page) {
                    if ($page->getPageId() === $pageId) {
                        $pagePathCodeList[] = $page->getCode();
                    }
                }
            }

            $surveyPath->setWeight($randomizedCombinations['weights'][$index]);
            $surveyPath->setPages($surveyPathPages);
            $surveyPath->setDebugPath(implode(',', $pagePathCodeList));

            $this->dm->persist($surveyPath);
        }

        $this->dm->flush();
    }

    public function getRandomWeightedElement(array $paths): ?Document\SurveyPath
    {
        $weightedValues = [];

        /** @var Document\SurveyPath $path */
        foreach ($paths as $index => $path) {
            $weightedValues[$index] = $path->getWeight();
        }

        $rand = mt_rand(0, (int) (array_sum($weightedValues) * 10000)) / 10000;
        $randLog = $rand;

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $paths[$key];
            }
        }

        throw new \Exception('Exception. Could not find path with random weight of ' . $randLog);
    }

    public function delete(Document\SurveyPath $surveyPath)
    {
        $this->dm->remove($surveyPath);
        $this->dm->flush();
    }

    public function deleteBySurveyId(int $surveyId)
    {
        $this->dm->createQueryBuilder(Document\SurveyPath::class)
                 ->remove()
                 ->field('surveyId')->equals($surveyId)
                 ->getQuery()
                 ->execute();
    }

    public function deleteBySurveyIdAndVersion(int $surveyId, int $version)
    {
        $this->dm->createQueryBuilder(Document\SurveyPath::class)
                 ->remove()
                 ->field('surveyId')->equals($surveyId)
                 ->field('version')->equals($version)
                 ->getQuery()
                 ->execute();
    }

    public function getRandomSurveyPath(Document\Survey $survey): ?Document\SurveyPath
    {
        $paths = $this->findAll($survey);

        if (count($paths)) {
            return $this->getRandomWeightedElement($paths);
        }

        return null;
    }

    private function findAll(Document\Survey $survey): ?array
    {
        return $this->dm->getRepository(Document\SurveyPath::class)->findBy(
            [
                'surveyId' => $survey->getSurveyId(),
                'version'  => $survey->getVersion()
            ],
            [
                'version' => 'DESC'
            ]
        );
    }
}
