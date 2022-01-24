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
        return [self::TYPE_SCREENOUT, self::TYPE_QUALITY_SCREENOUT];
    }

    public function getType():? string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, $this->getSupportedTypes())) {
            throw new \InvalidArgumentException(sprintf('Unsupported screenout type: "%s"', $type));
        }

        $this->type = $type;

        return $this;
    }

    public function getScreenoutId():? int
    {
        return $this->screenoutId;
    }

    public function setScreenoutId(int $screenoutId): self
    {
        $this->screenoutId = $screenoutId;

        return $this;
    }
}
