<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use JsonSerializable;

/**
 * @ODM\Document(collection="survey_stats"))
 * @ODM\UniqueIndex(keys={"surveyId"="asc", "version"="asc"})
 */
class SurveyStats  implements JsonSerializable
{
    /** @ODM\Id */
    private $id;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $surveyId;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $version;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $visits = 0;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $debugResponses = 0;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $testResponses = 0;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $liveResponses = 0;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $completes = 0;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTime();
    }

    public function jsonSerialize()
    {
        return [
            'id'             => $this->id,
            'surveyId'       => $this->surveyId,
            'version'        => $this->version,
            'visits'         => $this->visits,
            'debugResponses' => $this->debugResponses,
            'testResponses'  => $this->testResponses,
            'liveResponses'  => $this->liveResponses,
            'completes'      => $this->completes,
            'updatedAt'      => $this->updatedAt,
        ];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return SurveyStats
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getSurveyId(): int
    {
        return $this->surveyId;
    }

    /**
     * @param int $surveyId
     *
     * @return SurveyStats
     */
    public function setSurveyId(int $surveyId): SurveyStats
    {
        $this->surveyId = $surveyId;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     *
     * @return SurveyStats
     */
    public function setVersion(int $version): SurveyStats
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return int
     */
    public function getVisits(): int
    {
        return $this->visits;
    }

    /**
     * @param int $visits
     *
     * @return SurveyStats
     */
    public function setVisits(int $visits): SurveyStats
    {
        $this->visits = $visits;

        return $this;
    }

    /**
     * @return int
     */
    public function getDebugResponses(): int
    {
        return $this->debugResponses;
    }

    /**
     * @param int $debugResponses
     *
     * @return SurveyStats
     */
    public function setDebugResponses(int $debugResponses): SurveyStats
    {
        $this->debugResponses = $debugResponses;

        return $this;
    }

    /**
     * @return int
     */
    public function getTestResponses(): int
    {
        return $this->testResponses;
    }

    /**
     * @param int $testResponses
     *
     * @return SurveyStats
     */
    public function setTestResponses(int $testResponses): SurveyStats
    {
        $this->testResponses = $testResponses;

        return $this;
    }

    /**
     * @return int
     */
    public function getLiveResponses(): int
    {
        return $this->liveResponses;
    }

    /**
     * @param int $liveResponses
     *
     * @return SurveyStats
     */
    public function setLiveResponses(int $liveResponses): SurveyStats
    {
        $this->liveResponses = $liveResponses;

        return $this;
    }

    /**
     * @return int
     */
    public function getCompletes(): int
    {
        return $this->completes;
    }

    /**
     * @param int $completes
     *
     * @return SurveyStats
     */
    public function setCompletes(int $completes): SurveyStats
    {
        $this->completes = $completes;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return SurveyStats
     */
    public function setUpdatedAt(\DateTime $updatedAt): SurveyStats
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
