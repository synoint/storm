<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class Answer
{
    const FIELD_TYPE_TEXT     = 1;
    const FIELD_TYPE_TEXTAREA = 2;
    const FIELD_TYPE_RADIO    = 3;
    const FIELD_TYPE_CHECKBOX = 4;
    const FIELD_TYPE_SELECT   = 5;

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
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $answerFieldTypeId;

    /**
     * @var string|null
     *
     * @ODM\Field(type="string")
     */
    private $label;

    /**
     * @var string|null
     *
     * @ODM\Field(type="string")
     */
    private $rowLabel;

    /**
     * @var string|null
     *
     * @ODM\Field(type="string")
     */
    private $columnLabel;

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
    public function getStormMakerAnswerId():? int
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
    public function getSortOrder():? int
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
     * @return int
     */
    public function getAnswerFieldTypeId():? int
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
     * @return null|string
     */
    public function getLabel():? string
    {
        return $this->label;
    }

    /**
     * @param null|string $label
     *
     * @return Answer
     */
    public function setLabel(?string $label): Answer
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRowLabel(): ?string
    {
        return $this->rowLabel;
    }

    /**
     * @param null|string $rowLabel
     *
     * @return Answer
     */
    public function setRowLabel(?string $rowLabel): Answer
    {
        $this->rowLabel = $rowLabel;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getColumnLabel(): ?string
    {
        return $this->columnLabel;
    }

    /**
     * @param null|string $columnLabel
     *
     * @return Answer
     */
    public function setColumnLabel(?string $columnLabel): Answer
    {
        $this->columnLabel = $columnLabel;

        return $this;
    }


}
