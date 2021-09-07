<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class ResponseAnswer implements JsonSerializable
{
    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $questionId;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=ResponseAnswerValue::class)
     */
    private $answers;

    public function __construct(int $questionId, Collection $answers)
    {
        $this->questionId = $questionId;
        $this->answers    = $answers;
    }

    public function jsonSerialize(): array
    {
        return [
            'questionId' => $this->questionId,
            'values'     => $this->answers
        ];
    }

    public function getQuestionId(): int
    {
        return $this->questionId;
    }

    public function setQuestionId(int $questionId): self
    {
        $this->questionId = $questionId;

        return $this;
    }

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function setAnswers(Collection $answers): self
    {
        $this->answers = $answers;

        return $this;
    }
}
