<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="answer"))
 */
class Answer
{
    /**
     * @ODM\Id
     */
    private $id;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $stormMakerAnswerId;

    /**
     * @ODM\Field(type="string")
     */
    private $code;

    /**
     * @ODM\Field(type="string")
     */
    private $rowCode;

    /**
     * @ODM\Field(type="string")
     */
    private $columnCode;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\NotBlank
     */
    private $sortOrder;

    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    private $text;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $answerFieldTypeId;

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
     * @return Answer
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getStormMakerAnswerId(): int
    {
        return $this->stormMakerAnswerId;
    }

    /**
     * @param int $stormMakerAnswerId
     *
     * @return Answer
     */
    public function setStormMakerAnswerId(int $stormMakerAnswerId): Answer
    {
        $this->stormMakerAnswerId = $stormMakerAnswerId;

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
     * @return Answer
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRowCode()
    {
        return $this->rowCode;
    }

    /**
     * @param mixed $rowCode
     *
     * @return Answer
     */
    public function setRowCode($rowCode)
    {
        $this->rowCode = $rowCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getColumnCode()
    {
        return $this->columnCode;
    }

    /**
     * @param mixed $columnCode
     *
     * @return Answer
     */
    public function setColumnCode($columnCode)
    {
        $this->columnCode = $columnCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     *
     * @return Answer
     */
    public function setSortOrder(int $sortOrder): Answer
    {
        $this->sortOrder = $sortOrder;

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
     * @return Answer
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return int
     */
    public function getAnswerFieldTypeId(): int
    {
        return $this->answerFieldTypeId;
    }

    /**
     * @param int $answerFieldTypeId
     *
     * @return Answer
     */
    public function setAnswerFieldTypeId(int $answerFieldTypeId): Answer
    {
        $this->answerFieldTypeId = $answerFieldTypeId;

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
     * @return Answer
     */
    public function setAnswers(Collection $answers): Question
    {
        $this->answers = $answers;

        return $this;
    }
}
