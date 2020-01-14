<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private $questionId;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=ResponseAnswerValue::class)
     */
    private $answers;

    /**
     * @param int             $questionId
     * @param ArrayCollection $answers
     */
    public function __construct(int $questionId, ArrayCollection $answers)
    {
        $this->questionId = $questionId;
        $this->answers    = $answers;
    }

    /**
     * @return int
     */
    public function getQuestionId(): int
    {
        return $this->questionId;
    }

    /**
     * @param int $questionId
     *
     * @return ResponseAnswer
     */
    public function setQuestionId(int $questionId): ResponseAnswer
    {
        $this->questionId = $questionId;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    /**
     * @param Collection $answers
     *
     * @return ResponseAnswer
     */
    public function setAnswers(Collection $answers): ResponseAnswer
    {
        $this->answers = $answers;

        return $this;
    }
}
