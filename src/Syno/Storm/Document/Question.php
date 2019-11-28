<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ODM\Document(repositoryClass="Syno\Storm\Repository\Question", collection="question"))
 */
class Question
{
    /**
     * @ODM\Id
     */
    private $id;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $stormMakerQuestionId;

    /**
     * @ODM\Field(type="string")
     */
    private $code;

    /**
     * @ODM\Field(type="string")
     */
    private $text;

    /**
     * @var Collection
     *
     * @ODM\ReferenceMany(targetDocument="Question")
     */
    private $answers;

    /**
     * Survey constructor.
     */
    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

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
     * @return Question
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getStormMakerQuestionId(): int
    {
        return $this->stormMakerQuestionId;
    }

    /**
     * @param int $stormMakerQuestionId
     *
     * @return Question
     */
    public function setStormMakerQuestionId(int $stormMakerQuestionId): Question
    {
        $this->stormMakerQuestionId = $stormMakerQuestionId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     *
     * @return Question
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     *
     * @return Question
     */
    public function setText($text)
    {
        $this->text = $text;

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
     * @return Question
     */
    public function setAnswers(Collection $answers): Question
    {
        $this->answers = $answers;

        return $this;
    }
}
