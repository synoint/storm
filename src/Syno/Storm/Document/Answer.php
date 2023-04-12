<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Syno\Storm\Traits\TranslatableTrait;

/**
 * @ODM\EmbeddedDocument
 */
class Answer
{
    use TranslatableTrait;

    const FIELD_TYPE_TEXT = 1;
    const FIELD_TYPE_TEXTAREA = 2;
    const FIELD_TYPE_RADIO = 3;
    const FIELD_TYPE_CHECKBOX = 4;
    const FIELD_TYPE_SELECT = 5;

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
    private $answerId;

    /**
     * @ODM\Field(type="string")
     */
    private $code;

    /**
     * @ODM\Field(type="string")
     */
    private ?string $value = null;

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
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @Assert\NotNull
     */
    private $isExclusive = false;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @Assert\NotNull
     */
    private $isFreeText = false;

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
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @Assert\NotNull
     */
    private $hidden;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=AnswerTranslation::class)
     */
    protected $translations;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=ShowCondition::class)
     */
    private $showConditions;

    public function __construct()
    {
        $this->translations   = new ArrayCollection();
        $this->showConditions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getAnswerId(): ?int
    {
        return $this->answerId;
    }

    public function setAnswerId(int $answerId): self
    {
        $this->answerId = $answerId;

        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getValue():? string
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getRowCode()
    {
        return $this->rowCode;
    }

    public function setRowCode($rowCode): self
    {
        $this->rowCode = $rowCode;

        return $this;
    }

    public function getColumnCode()
    {
        return $this->columnCode;
    }

    public function setColumnCode($columnCode): self
    {
        $this->columnCode = $columnCode;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getIsExclusive(): bool
    {
        return $this->isExclusive;
    }

    public function setIsExclusive(bool $isExclusive): self
    {
        $this->isExclusive = $isExclusive;

        return $this;
    }

    public function getAnswerFieldTypeId(): ?int
    {
        return $this->answerFieldTypeId;
    }

    public function setAnswerFieldTypeId(int $answerFieldTypeId): self
    {
        $this->answerFieldTypeId = $answerFieldTypeId;

        return $this;
    }

    public function getIsFreeText(): bool
    {
        return $this->isFreeText;
    }

    public function setIsFreeText(bool $isFreeText): self
    {
        $this->isFreeText = $isFreeText;

        return $this;
    }

    public function getLabel(): ?string
    {
        /** @var AnswerTranslation $translation */
        $translation = $this->getTranslation();
        if (null !== $translation && !empty($translation->getLabel())) {

            return $translation->getLabel();
        }

        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getRowLabel(): ?string
    {
        /** @var AnswerTranslation $translation */
        $translation = $this->getTranslation();
        if (null !== $translation && !empty($translation->getRowLabel())) {

            return $translation->getRowLabel();
        }

        return $this->rowLabel;
    }

    public function setRowLabel(?string $rowLabel): self
    {
        $this->rowLabel = $rowLabel;

        return $this;
    }

    public function getColumnLabel(): ?string
    {
        /** @var AnswerTranslation $translation */
        $translation = $this->getTranslation();
        if (null !== $translation && !empty($translation->getColumnLabel())) {

            return $translation->getColumnLabel();
        }

        return $this->columnLabel;
    }

    public function setColumnLabel(?string $columnLabel): self
    {
        $this->columnLabel = $columnLabel;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function setShowConditions($showConditions): self
    {
        $this->showConditions = $showConditions;

        return $this;
    }

    public function getShowConditions(): Collection
    {
        return $this->showConditions;
    }

    public function hasMedia(): bool
    {
        return
            strpos($this->getLabel(), Page::VIDEO_TAG) !== false ||
            strpos($this->getLabel(), Page::AUDIO_TAG) !== false ||
            strpos($this->getRowLabel(), Page::VIDEO_TAG) !== false ||
            strpos($this->getRowLabel(), Page::AUDIO_TAG) !== false ||
            strpos($this->getColumnLabel(), Page::VIDEO_TAG) !== false ||
            strpos($this->getColumnLabel(), Page::AUDIO_TAG) !== false;
    }
}
