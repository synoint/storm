<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class PagePreview
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
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $locale;

    /**
     * @var Page
     *
     * @ODM\EmbedOne(targetDocument=Page::class)
     */
    private $page;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=Css::class)
     */
    private $css;

    public function __construct()
    {
        $this->page = new ArrayCollection();
        $this->css = new ArrayCollection();
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

    public function getPage()
    {
        return $this->page;
    }

    public function setPage(Collection $page): self
    {
        $this->page = $page->first();

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->page->getQuestions();
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

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}
