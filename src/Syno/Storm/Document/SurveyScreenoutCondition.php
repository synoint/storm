<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class SurveyScreenoutCondition
{
    /** @ODM\Id */
    private $id;

    /**
     * @ODM\Field(type="int")
     * @Assert\NotBlank
     */
    private $surveyScreenoutConditionId;

    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    private $rule;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getRule():? string
    {
        return $this->rule;
    }

    public function setRule(string $rule): self
    {
        $this->rule = $rule;

        return $this;
    }

    public function getSurveyScreenoutConditionId():? int
    {
        return $this->surveyScreenoutConditionId;
    }

    public function setSurveyScreenoutConditionId(int $surveyScreenoutConditionId): self
    {
        $this->surveyScreenoutConditionId = $surveyScreenoutConditionId;

        return $this;
    }
}
