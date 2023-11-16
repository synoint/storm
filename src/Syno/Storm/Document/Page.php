<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Syno\Storm\Traits\TranslatableTrait;

/**
 * @ODM\Document(collection="page", readOnly=true)
 * @ODM\Index(keys={"surveyId"="desc", "version"="desc"})
 */
class Page
{
    use TranslatableTrait;

    const VIDEO_TAG = '</video>';
    const AUDIO_TAG = '</audio>';

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
    private $pageId;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $surveyId;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $version;

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
     * @ODM\Field(type="string")
     */
    private $content;

    /**
     * @ODM\Field(type="string")
     */
    private $javascript;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=Question::class)
     */
    private $questions;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=PageTranslation::class)
     */
    protected $translations;

    public function __construct()
    {
        $this->questions    = new ArrayCollection();
        $this->translations = new ArrayCollection();
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

    /**
     * @return int
     */
    public function getPageId(): ?int
    {
        return $this->pageId;
    }

    /**
     * @param int $pageId
     *
     * @return Page
     */
    public function setPageId(int $pageId): Page
    {
        $this->pageId = $pageId;

        return $this;
    }

    public function getSurveyId(): int
    {
        return $this->surveyId;
    }

    public function setSurveyId(int $surveyId): void
    {
        $this->surveyId = $surveyId;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
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
     * @return Page
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     *
     * @return Page
     */
    public function setSortOrder(int $sortOrder): Page
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        $result = $this->content;

        /** @var PageTranslation $translation */
        $translation = $this->getTranslation();
        if (null !== $translation && $translation->getContent()) {
            $content = trim($translation->getContent());
            if (strlen($content)) {
                $result = $content;
            }
        }

        return $result;
    }

    public function hasContent(): bool
    {
        $content = $this->getContent();
        if ($content) {
            $content = trim($content);
        }

        return $content && strlen($content);
    }

    /**
     * @param mixed $content
     *
     * @return Page
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getJavascript()
    {
        return $this->javascript;
    }

    public function setJavascript(?string $javascript): self
    {
        $this->javascript = $javascript;

        return $this;
    }

    public function hasMedia(): bool
    {
        foreach ([self::AUDIO_TAG, self::VIDEO_TAG] as $mediaTag) {
            if ($this->getContent() && str_contains($this->getContent(), $mediaTag)) {
                return true;
            }
        }

        foreach ($this->getQuestions() as $question) {
            if ($question->hasMedia()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @return Collection|Question[]
     */
    public function getVisibleQuestions(): Collection
    {
        return $this->questions->filter(function (Question $question) {
            return !$question->isHidden();
        });
    }

    public function setQuestions($questions): self
    {
        if (is_array($questions)) {
            foreach ($questions as $question) {
                $this->questions->add($question);
            }

            return $this;
        }

        $this->questions = $questions;

        return $this;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations($translations): void
    {
        $this->translations = $translations;
    }
}
