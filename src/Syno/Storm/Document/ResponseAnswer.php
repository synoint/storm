<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class ResponseAnswer
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
     * @return ResponseAnswer
     */
    public function setAnswerId(int $answerId): ResponseAnswer
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
     * @return ResponseAnswer
     */
    public function setValue(string $value): ResponseAnswer
    {
        $this->value = $value;

        return $this;
    }

}
