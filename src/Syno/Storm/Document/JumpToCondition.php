<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class JumpToCondition
{

    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    private $rule;

    /**
     * @ODM\Field(type="integer")
     * @Assert\NotBlank
     */
    private $destination;


    /**
     * @ODM\Field(type="collection")
     */
    private $ruleQuestionIds;


    /**
     * @return mixed
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param mixed $rule
     *
     * @return self
     */
    public function setRule($rule)
    {
        $this->rule = $rule;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     *
     * @return self
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRuleQuestionIds()
    {
        return $this->ruleQuestionIds;
    }

    /**
     * @param mixed $ruleQuestionIds
     *
     * @return self
     */
    public function setRuleQuestionIds($ruleQuestionIds)
    {
        $this->ruleQuestionIds = $ruleQuestionIds;

        return $this;
    }
}
