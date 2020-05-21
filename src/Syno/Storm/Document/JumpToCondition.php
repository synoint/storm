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
    const DESTINATION_TYPE_QUESTION      = 'question';
    const DESTINATION_TYPE_END_OF_SURVEY = 'end_of_survey';

    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    private $rule;

    /**
     * @ODM\Field(type="integer")
     */
    private $destination;

    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    private $destinationType;

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
    public function getDestinationType()
    {
        return $this->destinationType;
    }

    /**
     * @param mixed $destinationType
     *
     * @return self
     */
    public function setDestinationType($destinationType)
    {
        $this->destinationType = $destinationType;

        return $this;
    }
}
