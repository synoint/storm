<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class SurveyPreview
{
    /** @ODM\Id */
    private $id;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $logoPath;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $progress;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $isFirstPage;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $isLastPage;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $hasBackButton;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $publicTitle;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=Page::class)
     */
    private $pages;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=Css::class)
     */
    private $css;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->css   = new ArrayCollection();
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

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function setLogoPath(string $logoPath): self
    {
        $this->logoPath = $logoPath;

        return $this;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): self
    {
        $this->progress = $progress;

        return $this;
    }

    public function isFirstPage(): int
    {
        return $this->isFirstPage;
    }

    public function setIsFirstPage(int $isFirstPage): self
    {
        $this->isFirstPage = $isFirstPage;

        return $this;
    }

    public function isLastPage(): int
    {
        return $this->isFirstPage;
    }

    public function setIsLastPage(int $isFirstPage): self
    {
        $this->isFirstPage = $isFirstPage;

        return $this;
    }

    public function hasBackButton(): int
    {
        return $this->hasBackButton;
    }

    public function setHasBackButton(int $hasBackButton): self
    {
        $this->hasBackButton = $hasBackButton;

        return $this;
    }

    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function setPages($pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function getPage(int $pageId): ?Page
    {
        $result = null;
        foreach ($this->pages as $page) {
            if ($pageId === $page->getPageId()) {
                $result = $page;
                break;
            }
        }

        return $result;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        $questions = new ArrayCollection();

        /** @var Page $page */
        foreach ($this->pages as $page) {
            $questions = new ArrayCollection(array_merge($questions->toArray(), $page->getQuestions()->toArray()));
        }

        return $questions;
    }

    public function getPublicTitle(): ?string
    {
        return $this->publicTitle;
    }

    public function setPublicTitle($publicTitle): self
    {
        $this->publicTitle = $publicTitle;

        return $this;
    }

    public function getCss(): Collection
    {
        return $this->css;
    }

    public function setCss(Collection $css): self
    {
        $this->css = $css;

        return $this;
    }
}
