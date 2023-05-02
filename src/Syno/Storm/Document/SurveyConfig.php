<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="survey_config"))
 */
class SurveyConfig
{
    public const EMAIL_NOTIFICATION = 'emailNotification';

    /** @ODM\Id */
    private $id;

    /**
     * @var int
     *
     * @ODM\Field(type="int") @ODM\Index
     */
    private $surveyId;

    /**
     * @var string
     *
     * @ODM\Field(type="string") @ODM\Index
     */
    private $key;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $value;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getSurveyId(): int
    {
        return $this->surveyId;
    }

    public function setSurveyId(int $surveyId): void
    {
        $this->surveyId = $surveyId;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
