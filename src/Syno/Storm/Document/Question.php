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
class Question
{
    use TranslatableTrait;

    const TYPE_SINGLE_CHOICE          = 1;
    const TYPE_MULTIPLE_CHOICE        = 2;
    const TYPE_SINGLE_CHOICE_MATRIX   = 3;
    const TYPE_MULTIPLE_CHOICE_MATRIX = 4;
    const TYPE_TEXT                   = 5;
    const TYPE_LINEAR_SCALE           = 6;
    const TYPE_LINEAR_SCALE_MATRIX    = 7;
    const TYPE_GABOR_GRANGER          = 8;
    const TYPE_MULTI_TEXT             = 9;

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
     * @ODM\Field(type="bool")
     * @Assert\NotNull
     */
    private $required = true;

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
    private $randomizeAnswers = false;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @Assert\NotNull
     */
    private $randomizeRows = false;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @Assert\NotNull
     */
    private $randomizeColumns = false;

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
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $scoreModuleId;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $scoreModuleParentId;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=Answer::class)
     */
    private $answers;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=ShowCondition::class)
     */
    private $showConditions;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=ScreenoutCondition::class)
     */
    private $screenoutConditions;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=JumpToCondition::class)
     */
    private $jumpToConditions;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=QuestionTranslation::class)
     */
    protected $translations;


    public function __construct()
    {
        $this->answers             = new ArrayCollection();
        $this->showConditions      = new ArrayCollection();
        $this->screenoutConditions = new ArrayCollection();
        $this->jumpToConditions    = new ArrayCollection();
        $this->translations        = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getQuestionId(): ?int
    {
        return $this->questionId;
    }

    public function setQuestionId(int $questionId): self
    {
        $this->questionId = $questionId;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getText(): ?string
    {
        /** @var QuestionTranslation $translation */
        $translation = $this->getTranslation();
        if (null !== $translation && !empty($translation->getText())) {

            return $translation->getText();
        }

        return $this->text;
    }

    public function setText($text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getShowConditions(): Collection
    {
        return $this->showConditions;
    }

    public function setShowConditions($showConditions): self
    {
        $this->showConditions = $showConditions;

        return $this;
    }

    public function getJumpToConditions(): Collection
    {
        return $this->jumpToConditions;
    }

    public function setJumpToConditions($jumpToConditions): self
    {
        $this->jumpToConditions = $jumpToConditions;

        return $this;
    }

    public function getScreenoutConditions(): Collection
    {
        return $this->screenoutConditions;
    }

    public function setScreenoutConditions($screenoutConditions): self
    {
        $this->screenoutConditions = $screenoutConditions;

        return $this;
    }

    public function getQuestionTypeId(): ?int
    {
        return $this->questionTypeId;
    }

    public function setQuestionTypeId(int $questionTypeId): self
    {
        $this->questionTypeId = $questionTypeId;

        return $this;
    }

    public function getScoreModuleId(): ?int
    {
        return $this->scoreModuleId;
    }

    public function setScoreModuleId(int $scoreModuleId): self
    {
        $this->scoreModuleId = $scoreModuleId;

        return $this;
    }

    public function getScoreModuleParentId(): ?int
    {
        return $this->scoreModuleParentId;
    }

    public function setScoreModuleParentId(int $scoreModuleParentId): self
    {
        $this->scoreModuleParentId = $scoreModuleParentId;

        return $this;
    }

    public function isMatrix(): bool
    {
        return in_array($this->questionTypeId, [self::TYPE_SINGLE_CHOICE_MATRIX, self::TYPE_MULTIPLE_CHOICE_MATRIX]);
    }

    public function isText(): bool
    {
        return self::TYPE_TEXT === $this->questionTypeId;
    }

    public function isMultiText(): bool
    {
        return self::TYPE_MULTI_TEXT === $this->questionTypeId;
    }

    public function isLinearScale(): bool
    {
        return self::TYPE_LINEAR_SCALE === $this->questionTypeId;
    }

    public function isLinearScaleMatrix(): bool
    {
        return self::TYPE_LINEAR_SCALE_MATRIX === $this->questionTypeId;
    }

    public function getRandomizeAnswers(): bool
    {
        return $this->randomizeAnswers;
    }

    public function setRandomizeAnswers(bool $randomizeAnswers): self
    {
        $this->randomizeAnswers = $randomizeAnswers;

        return $this;
    }

    public function getRandomizeRows(): bool
    {
        return $this->randomizeRows;
    }

    public function setRandomizeRows(bool $randomizeRows): self
    {
        $this->randomizeRows = $randomizeRows;

        return $this;
    }

    public function getRandomizeColumns(): bool
    {
        return $this->randomizeColumns;
    }

    public function setRandomizeColumns(bool $randomizeColumns): self
    {
        $this->randomizeColumns = $randomizeColumns;

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers->filter(function (Answer $answer) {
            return !$answer->isHidden();
        });
    }

    public function getChoices(): array
    {
        $choices = [];
        /** @var Answer $answer */
        foreach ($this->getAnswers() as $answer) {
            $choices[$answer->getLabel()] = $answer->getAnswerId();
        }

        return $choices;
    }

    public function answerIdExists(int $answerId): bool
    {
        foreach ($this->getAnswers() as $answer) {
            if ($answer->getAnswerId() === $answerId) {
                return true;
            }
        }

        return false;
    }

    public function answerCodeExists(string $answerCode): bool
    {
        /** @var Answer $answer */
        foreach ($this->getAnswers() as $answer) {
            if ($answer->getCode() === $answerCode) {
                return true;
            }
        }

        return false;
    }

    public function getAnswer(int $answerId): ?Answer
    {
        foreach ($this->getAnswers() as $answer) {
            if ($answer->getAnswerId() == $answerId) {
                return $answer;
            }
        }
        return null;
    }

    public function getAnswerByCode(string $answerCode): ?Answer
    {
        foreach ($this->getAnswers() as $answer) {
            if ($answer->getCode() == $answerCode) {
                return $answer;
            }
        }

        return null;
    }

    public function getAnswerByRowAndColumn($row, $column): ?Answer
    {
        foreach ($this->getAnswers() as $answer) {
            if ($answer->getRowCode() == $row && $answer->getColumnCode() == $column) {
                return $answer;
            }
        }

        return null;
    }

    public function setAnswers($answers): self
    {
        if (is_array($answers)) {
            foreach ($answers as $answer) {
                $this->answers->add($answer);
            }

            return $this;
        }


        $this->answers = $answers;

        return $this;
    }

    public function getRows(): array
    {
        $result = [];

        /** @var Answer $answer */
        foreach ($this->answers as $answer) {
            if ('' !== $answer->getRowCode() && !$answer->isRowHidden()) {
                $result[$answer->getRowCode()] = $answer->getRowLabel();
            }
        }

        return $result;
    }

    public function getColumns(): array
    {
        $result = [];

        /** @var Answer $answer */
        foreach ($this->answers as $answer) {
            if ('' !== $answer->getColumnCode() && !$answer->isColumnHidden()) {
                $result[$answer->getColumnCode()] = $answer->getColumnLabel();
            }
        }

        return $result;
    }

    public function getMatrixAnswer(string $rowCode, string $columnCode): Answer
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

    public function getInputName(string $suffix = null): string
    {
        return $this->code . ((null !== $suffix) ? '_' . $suffix : '');
    }

    public function containsSelectField(): bool
    {
        if ($this->answers->count()) {
            return $this->answers->first()->getAnswerFieldTypeId() === Answer::FIELD_TYPE_SELECT;
        }

        return false;
    }

    public function hasMedia(): bool
    {
        foreach ([Page::AUDIO_TAG, Page::VIDEO_TAG] as $mediaTag) {
            if ($this->getText() && str_contains($this->getText(), $mediaTag)) {
                return true;
            }
        }

        foreach ($this->answers as $answer) {
            if ($answer->hasMedia()) {
                return true;
            }
        }

        return false;
    }
}
