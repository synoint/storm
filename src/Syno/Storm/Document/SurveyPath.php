<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="survey_path", readOnly=true)
 * @ODM\Index(keys={"surveyId"="asc", "version"="asc"})
 * @ODM\UniqueIndex(keys={"surveyPathId"="asc"})
 */
class SurveyPath implements JsonSerializable
{
    /** @ODM\Id */
    private $id;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $surveyId;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $surveyPathId;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $version;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=SurveyPathPage::class)
     */
    private $pages;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $debugPath;

    /**
     * @var float
     *
     * @ODM\Field(type="float")
     */
    private $weight = 0;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
    }

    public function jsonSerialize(): array
    {
        return [
            'id'           => $this->id,
            'surveyPathId' => $this->surveyPathId,
            'surveyId'     => $this->surveyId,
            'version'      => $this->version,
            'pages'        => $this->pages,
            'weight'       => $this->weight,
        ];
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

    public function getSurveyPathId(): ?string
    {
        return $this->surveyPathId;
    }

    public function setSurveyPathId($surveyPathId): self
    {
        $this->surveyPathId = $surveyPathId;

        return $this;
    }

    public function getSurveyId(): ?int
    {
        return $this->surveyId;
    }

    public function setSurveyId(int $surveyId): self
    {
        $this->surveyId = $surveyId;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return Collection|Page[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function getDebugPath(): string
    {
        return $this->debugPath;
    }

    public function setDebugPath(string $path): self
    {
        $this->debugPath = $path;

        return $this;
    }

    public function getFirstPage(): ?SurveyPathPage
    {
        if ($this->pages->count()) {
            return $this->getPages()->first();
        }

        return null;
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

    public function getNextPage(int $pageId): ?Page
    {
        $result = null;
        $pick   = false;
        foreach ($this->pages as $page) {
            if ($pick) {
                $result = $page;
                break;
            }
            if ($pageId === $page->getPageId()) {
                $pick = true;
            }
        }

        return $result;
    }

    public function isFirstPage(int $pageId): bool
    {
        return $pageId === $this->pages->first()->getPageId();
    }

    public function isLastPage(int $pageId): bool
    {
        return $pageId === $this->pages->last()->getPageId();
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }
}
