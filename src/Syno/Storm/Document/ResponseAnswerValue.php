<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use JsonSerializable;
/**
 * @ODM\EmbeddedDocument
 */
class ResponseAnswerValue implements JsonSerializable
{
    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $answerId;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $value;

    /**
     * @param int    $answerId
     * @param string $value
     */
    public function __construct(int $answerId, string $value = null)
    {
        $this->answerId = $answerId;
        $this->value    = $value;
    }

    public function jsonSerialize()
    {
        return [
            'answerId' => $this->answerId,
            'value'    => $this->value
        ];
    }

    /**
     * @return int
     */
    public function getAnswerId(): int
    {
        return $this->answerId;
    }

    /**
     * @param int $answerId
     *
     * @return self
     */
    public function setAnswerId(int $answerId): self
    {
        $this->answerId = $answerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

}
