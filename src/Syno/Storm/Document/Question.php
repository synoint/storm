<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="question"))
 */
class Question
{
    const TYPE_SINGLE_CHOICE          = 1;
    const TYPE_MULTIPLE_CHOICE        = 2;
    const TYPE_SINGLE_CHOICE_MATRIX   = 3;
    const TYPE_MULTIPLE_CHOICE_MATRIX = 4;
    const TYPE_TEXT                   = 5;

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
    private $stormMakerQuestionId;

    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    private $code;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\NotBlank
     */
    private $sortOrder;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean")
     * @Assert\NotNull
     */
    private $required = true;

    /**
     * @var string
     *
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
    private $questionTypeId;

    /**
     * @var Collection
     *
     * @ODM\ReferenceMany(targetDocument="Answer", storeAs="id", cascade={"persist", "remove"})
     */
    private $answers;


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
    public function getStormMakerQuestionId():? int
    {
        return $this->stormMakerQuestionId;
    }

    /**
     * @param int $stormMakerQuestionId
     *
     * @return Question
     */
    public function setStormMakerQuestionId(int $stormMakerQuestionId): self
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
     * @return int
     */
    public function getSortOrder():? int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     *
     * @return Question
     */
    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return Question
     */
    public function setRequired(bool $required): self
    {
        $this->required = $required;

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
     * @return int
     */
    public function getQuestionTypeId():? int
    {
        return $this->questionTypeId;
    }

    /**
     * @param int $questionTypeId
     *
     * @return Question
     */
    public function setQuestionTypeId(int $questionTypeId): self
    {
        $this->questionTypeId = $questionTypeId;

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
     * @param $answers
     *
     * @return Question
     */
    public function setAnswers($answers): self
    {
        $this->answers = $answers;

        return $this;
    }
}
