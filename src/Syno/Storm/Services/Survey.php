<?php

namespace Syno\Storm\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Syno\Storm\Document;

class Survey
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

    /**
     * @return Document\Survey
     */
    public function getNew(): Document\Survey
    {
        return new Document\Survey();
    }

    /**
     * @param Document\Survey $survey
     */
    public function save(Document\Survey $survey)
    {
        $this->dm->persist($survey);
        $this->dm->flush();
    }

    /**
     * @param int $stormMakerSurveyId
     * @param int $version
     *
     * @return null|Document\Survey
     */
    public function findByStormMakerIdAndVersion(int $stormMakerSurveyId, int $version)
    {
        return $this->dm->getRepository(Document\Survey::class)->findOneBy(
            [
                'stormMakerSurveyId' => $stormMakerSurveyId,
                'version' => $version,
            ]
        );
    }

    /**
     * @param Document\Survey $survey
     */
    public function delete(Document\Survey $survey)
    {
        $this->dm->remove($survey);
        $this->dm->flush();
    }


}
