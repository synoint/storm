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

    public function __construct(int $answerId, string $value = null)
    {
        $this->answerId = $answerId;
        $this->value    = $value;
    }

    public function jsonSerialize(): array
    {
        if (null !== $this->value) {
            return [
                'answerId' => $this->answerId,
                'value'    => $this->value
            ];
        }

        return [
            'answerId' => $this->answerId
        ];
    }

    public function getAnswerId(): int
    {
        return $this->answerId;
    }

    public function setAnswerId(int $answerId): self
    {
        $this->answerId = $answerId;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

}
