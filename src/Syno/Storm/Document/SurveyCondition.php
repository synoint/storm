<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class SurveyCondition
{
    const TYPE_START_CONDITION  = 'start_of_survey';
    const TYPE_END_CONDITION    = 'end_of_survey';

    /** @ODM\Id */
    private $id;

    /**
     * @ODM\Field(type="int")
     * @Assert\NotBlank
     */
    private $surveyConditionId;

    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    private $rule;

    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    private $type;

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

    public function getSupportedTypes(): array
    {
        return [self::TYPE_END_CONDITION, self::TYPE_START_CONDITION];
    }

    public function getType():? string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, $this->getSupportedTypes())) {
            throw new \InvalidArgumentException(sprintf('Unsupported survey condition type: "%s"', $type));
        }

        $this->type = $type;

        return $this;
    }

    public function getSurveyConditionId():? int
    {
        return $this->surveyConditionId;
    }

    public function setSurveyConditionId(int $surveyConditionId): self
    {
        $this->surveyConditionId = $surveyConditionId;

        return $this;
    }
}
