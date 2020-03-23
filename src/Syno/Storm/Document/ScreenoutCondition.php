<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class ScreenoutCondition
{
    const TYPE_SCREENOUT            = 'screenout';
    const TYPE_QUALITY_SCREENOUT    = 'quality_screenout';

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
     * @return array
     */
    public function getSupportedTypes()
    {
        return [self::TYPE_SCREENOUT, self::TYPE_QUALITY_SCREENOUT];
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return self
     */
    public function seType($type)
    {
        if(in_array($type, $this->getSupportedTypes())) {
            $this->type = $type;
        } else {
            throw new \InvalidArgumentException(sprintf('Unsupported screenout type: "%s"', $type));
        }

        return $this;
    }
}
