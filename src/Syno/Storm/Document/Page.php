<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ODM\Document(repositoryClass="Syno\Storm\Repository\Page", collection="page"))
 */
class Page
{
    /**
     * @ODM\Id
     */
    private $id;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $stormMakerPageId;

    /**
     * @ODM\Field(type="string")
     */
    private $code;

    /**
     * @ODM\Field(type="string")
     */
    private $content;

    /**
     * @var Collection
     *
     * @ODM\ReferenceMany(targetDocument="Question")
     */
    private $questions;

    /**
     * Survey constructor.
     */
    public function __construct()
    {
        $this->questions = new ArrayCollection();
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
    public function getStormMakerPageId(): int
    {
        return $this->stormMakerPageId;
    }

    /**
     * @param int $stormMakerPageId
     *
     * @return Page
     */
    public function setStormMakerPageId(int $stormMakerPageId): Page
    {
        $this->stormMakerPageId = $stormMakerPageId;

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
     * @return mixed
     */
    public function getContent()
    {
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
     * @param Collection $questions
     *
     * @return Page
     */
    public function setQuestions(Collection $questions): Page
    {
        $this->questions = $questions;

        return $this;
    }
}
