<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class ShowCondition
{
    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    private $rule;

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
}
