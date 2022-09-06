<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class SurveyCompleteCondition
{
    /** @ODM\Id */
    private $id;

    /**
     * @ODM\Field(type="int")
     * @Assert\NotBlank
     */
    private $surveyCompleteConditionId;

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

    public function getSurveyCompleteConditionId():? int
    {
        return $this->surveyCompleteConditionId;
    }

    public function setSurveyCompleteConditionId(int $surveyCompleteConditionId): self
    {
        $this->surveyCompleteConditionId = $surveyCompleteConditionId;

        return $this;
    }
}
