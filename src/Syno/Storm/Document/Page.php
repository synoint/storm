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
class Page
{
    use TranslatableTrait;

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
     * @return Page
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageId():? int
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
    public function getSortOrder():? int
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
        /** @var PageTranslation $translation */
        $translation = $this->getTranslation();
        if (null !== $translation && !empty($translation->getContent())) {

            return $translation->getContent();
        }

        return $this->content;
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

    /**
     * @return Collection
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @param $questions
     *
     * @return self
     */
    public function setQuestions($questions): self
    {
        $this->questions = $questions;

        return $this;
    }

    public function sortQuestionsAndAnswers()
    {
        $this->questions = $this->sortQuestions();
        /** @var Question $question */
        foreach ($this->questions as $question) {
            $question->sortOutAnswers();
        }
    }

    public function sortQuestions()
    {
        $iterator = $this->questions->getIterator();

        $iterator->uasort(function ($a, $b) {
            /** @var Answer $a */
            /** @var Answer $b */
            return ($a->getSortOrder() < $b->getSortOrder()) ? -1 : 1;
        });
        return new ArrayCollection(iterator_to_array($iterator));
    }
}
