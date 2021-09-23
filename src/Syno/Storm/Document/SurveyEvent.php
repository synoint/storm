<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\Document(collection="survey_event"))
 */
class SurveyEvent implements JsonSerializable
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
     * @ODM\Field(type="int")
     * @ODM\Index
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
     * @ODM\Index
     */
    private $event;

    public function __construct(string $surveyId, int $version, string $event)
    {
        $this->time     = new \DateTime();
        $this->surveyId = $surveyId;
        $this->version  = $version;
        $this->event    = $event;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'       => $this->id,
            'time'     => $this->time->format('Y-m-d H:i:s.v'),
            'surveyId' => $this->surveyId,
            'version'  => $this->version,
            'event'    => $this->event
        ];
    }
}
