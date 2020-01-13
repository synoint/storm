<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
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
    private $questionId;

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
     * @ODM\EmbedMany(targetDocument=Answer::class)
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
    public function getQuestionId():? int
    {
        return $this->questionId;
    }

    /**
     * @param int $questionId
     *
     * @return Question
     */
    public function setQuestionId(int $questionId): self
    {
        $this->questionId = $questionId;

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

    public function isMatrix()
    {
        return in_array($this->questionTypeId, [self::TYPE_SINGLE_CHOICE_MATRIX, self::TYPE_MULTIPLE_CHOICE_MATRIX]);
    }

    /**
     * @return Collection
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    /**
     * @return array
     */
    public function getChoices(): array
    {
        $choices = [];
        /** @var Answer $answer */
        foreach ($this->getAnswers() as $answer) {
            $choices[$answer->getLabel()] = $answer->getCode();
        }

        return $choices;
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

    public function getRows()
    {
        $result = [];
        /** @var Answer $answer */
        foreach ($this->answers as $answer) {
            if (!empty($answer->getRowCode())) {
                $result[$answer->getRowCode()] = $answer->getRowLabel();
            }
        }

        return $result;
    }

    public function getColumns()
    {
        $result = [];
        /** @var Answer $answer */
        foreach ($this->answers as $answer) {
            if (!empty($answer->getColumnCode())) {
                $result[$answer->getColumnCode()] = $answer->getColumnLabel();
            }
        }

        return $result;
    }

    /**
     * @param string $rowCode
     * @param string $columnCode
     *
     * @return Answer
     */
    public function getMatrixAnswer(string $rowCode, string $columnCode)
    {
        /** @var Answer $answer */
        foreach ($this->answers as $answer) {
            if ($rowCode === $answer->getRowCode() && $columnCode === $answer->getColumnCode()) {
                return $answer;
            }
        }

        throw new \InvalidArgumentException(
            sprintf('No answer ID was found by row: %s and column: %s', $rowCode, $columnCode)
        );
    }

    public function getMatrixAnswerInputName(string $rowCode)
    {
        if (self::TYPE_SINGLE_CHOICE_MATRIX === $this->questionTypeId) {
            return 'a_' . $rowCode;
        }

        if (self::TYPE_MULTIPLE_CHOICE_MATRIX === $this->questionTypeId) {
            return 'a_' . $rowCode . '[]';
        }

        throw new \LogicException(sprintf('This question is not a matrix type question'));
    }
}
