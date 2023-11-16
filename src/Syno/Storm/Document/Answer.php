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

    public const FIELD_TYPE_TEXT            = 1;
    public const FIELD_TYPE_TEXTAREA        = 2;
    public const FIELD_TYPE_RADIO           = 3;
    public const FIELD_TYPE_CHECKBOX        = 4;
    public const FIELD_TYPE_SELECT          = 5;
    public const FIELD_TYPE_CUSTOM          = 6;
    public const FIELD_TYPE_EMAIL           = 7;
    public const FIELD_TYPE_PHONE           = 8;
    public const FIELD_TYPE_FIRST_LAST_NAME = 9;

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
    private $hidden = false;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @Assert\NotNull
     */
    private $rowHidden = false;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @Assert\NotNull
     */
    private $columnHidden = false;

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

    public function getValue(): ?string
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
        $result = $this->label;

        /** @var AnswerTranslation $translation */
        $translation = $this->getTranslation();
        if ($translation && $translation->getLabel()) {
            $translatedLabel = trim($translation->getLabel());
            if (strlen($translatedLabel)) {
                $result = $translatedLabel;
            }
        }

        return $result;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getRowLabel(): ?string
    {
        $result = $this->label;

        /** @var AnswerTranslation $translation */
        $translation = $this->getTranslation();
        if ($translation && $translation->getRowLabel()) {
            $translatedLabel = trim($translation->getRowLabel());
            if (strlen($translatedLabel)) {
                $result = $translatedLabel;
            }
        }

        return $result;
    }

    public function setRowLabel(?string $rowLabel): self
    {
        $this->rowLabel = $rowLabel;

        return $this;
    }

    public function getColumnLabel(): ?string
    {
        $result = $this->columnLabel;

        /** @var AnswerTranslation $translation */
        $translation = $this->getTranslation();
        if ($translation && $translation->getColumnLabel()) {
            $translatedLabel = trim($translation->getColumnLabel());
            if (strlen($translatedLabel)) {
                $result = $translatedLabel;
            }
        }

        return $result;
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

    public function isRowHidden(): bool
    {
        return $this->rowHidden;
    }

    public function setRowHidden(bool $rowHidden): void
    {
        $this->rowHidden = $rowHidden;
    }

    public function isColumnHidden(): bool
    {
        return $this->columnHidden;
    }

    public function setColumnHidden(bool $columnHidden): void
    {
        $this->columnHidden = $columnHidden;
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
        foreach ([Page::AUDIO_TAG, Page::VIDEO_TAG] as $mediaTag) {
            if ($this->getLabel() && str_contains($this->getLabel(), $mediaTag)) {
                return true;
            }
            if ($this->getRowLabel() && str_contains($this->getRowLabel(), $mediaTag)) {
                return true;
            }
            if ($this->getColumnLabel() && str_contains($this->getColumnLabel(), $mediaTag)) {
                return true;
            }
        }

        return false;
    }
}
