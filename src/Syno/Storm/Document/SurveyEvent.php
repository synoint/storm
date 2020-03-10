<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="survey_event"))
 * @ODM\UniqueIndex(keys={"surveyId"="asc", "version"="asc"})
 */
class SurveyEvent
{
    /** @ODM\Id */
    private $id;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date")
     */
    private $time;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $surveyId;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $version;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $event;

    /**
     * @param string $surveyId
     * @param int    $version
     * @param string $event
     */
    public function __construct(string $surveyId, int $version, string $event)
    {
        $this->time     = new \DateTime();
        $this->surveyId = $surveyId;
        $this->version  = $version;
        $this->event    = $event;
    }


}
