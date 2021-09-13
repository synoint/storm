<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class ScreenoutCondition
{
    const TYPE_SCREENOUT            = 'screenout';
    const TYPE_QUALITY_SCREENOUT    = 'quality_screenout';

    /** @ODM\Id */
    private $id;

    /**
     * @ODM\Field(type="int")
     * @Assert\NotBlank
     */
    private $screenoutId;

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

    /**
     * @param mixed $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type)
    {
        if(in_array($type, $this->getSupportedTypes())) {
            $this->type = $type;
        } else {
            throw new \InvalidArgumentException(sprintf('Unsupported screenout type: "%s"', $type));
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getScreenoutId()
    {
        return $this->screenoutId;
    }

    /**
     * @param int $screenoutId
     *
     * @return self
     */
    public function setScreenoutId($screenoutId)
    {
        $this->screenoutId = $screenoutId;

        return $this;
    }
}
