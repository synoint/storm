<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class SurveyPath
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function getNew(): Document\SurveyPath
    {
        $surveyPath = new Document\SurveyPath();
        $surveyPath->setSurveyPathId(bin2hex(random_bytes(8)));

        return $surveyPath;
    }

    public function save(Document\SurveyPath $surveyPath)
    {
        $this->dm->persist($surveyPath);
        $this->dm->flush();
    }

    public function find(Document\Survey $survey): ?array
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

    public function delete(Document\SurveyPath $surveyPath)
    {
        $this->dm->remove($surveyPath);
        $this->dm->flush();
    }

    public function getRandomWeightedElement(array $paths): ?Document\SurveyPath
    {
        $weightedValues = [];
        /** @var Document\SurveyPath $path */
        foreach ($paths as $index => $path) {
            $weightedValues[$index] = $path->getWeight();
        }

        $rand = mt_rand(1, (int) array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $paths[$key];
            }
        }

        return null;
    }
}
