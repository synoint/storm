<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Syno\Storm\Traits\TranslatableTrait;

/**
 * @ODM\EmbeddedDocument
 */
class SurveyPage implements PageInterface
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
     * @return SurveyPage
     */
    public function setId($id)
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
     * @return SurveyPage
     */
    public function setPageId(int $pageId): SurveyPage
    {
        $this->pageId = $pageId;

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
     * @return SurveyPage
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
     * @return SurveyPage
     */
    public function setSortOrder(int $sortOrder): SurveyPage
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        /** @var PageTranslation $translation */
        $translation = $this->getTranslation();
        if (null !== $translation && !empty($translation->getContent())) {

            return $translation->getContent();
        }

        return $this->content;
    }

    public function hasContent(): bool
    {
        /** @var PageTranslation $translation */
        $translation = $this->getTranslation();
        if (null !== $translation && !empty($translation->getContent())) {
            return (bool)strlen(trim($translation->getContent()));
        }

        return (bool)strlen(trim($this->content));
    }

    /**
     * @param mixed $content
     *
     * @return SurveyPage
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

    public function setJavascript(string $javascript): self
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
}
