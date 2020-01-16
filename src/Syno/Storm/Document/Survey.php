<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JsonSerializable;

/**
 * @ODM\Document(collection="survey"))
 * @ODM\UniqueIndex(keys={"surveyId"="asc", "version"="asc"})
 */
class Survey implements JsonSerializable
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
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $version;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean")
     * @Assert\NotNull
     */
    private $published = false;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=Page::class)
     */
    private $pages;

    /**
     * @var Config
     *
     * @ODM\EmbedOne(targetDocument=Config::class)
     */
    private $config;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=HiddenValue::class)
     */
    private $hiddenValues;

    public function __construct()
    {
        $this->pages        = new ArrayCollection();
        $this->hiddenValues = new ArrayCollection();
    }

    public function jsonSerialize()
    {
        return [
            'id'           => $this->id,
            'surveyId'     => $this->surveyId,
            'version'      => $this->version,
            'published'    => $this->published,
            'config'       => $this->config,
            'hiddenValues' => $this->hiddenValues

        ];
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
     * @return Survey
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getSurveyId():? int
    {
        return $this->surveyId;
    }

    /**
     * @param int $surveyId
     *
     * @return Survey
     */
    public function setSurveyId(int $surveyId): Survey
    {
        $this->surveyId = $surveyId;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion():? int
    {
        return $this->version;
    }

    /**
     * @param int $version
     *
     * @return Survey
     */
    public function setVersion(int $version): Survey
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * @param bool $published
     *
     * @return Survey
     */
    public function setPublished(bool $published): Survey
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param $pages
     *
     * @return Survey
     */
    public function setPages($pages): Survey
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * @param int $pageId
     *
     * @return Page|null
     */
    public function getPage(int $pageId)
    {
        $result = null;
        /** @var Page $page */
        foreach ($this->pages as $page) {
            if ($pageId === $page->getPageId()) {
                $result = $page;
                break;
            }
        }

        return $result;
    }

    /**
     * @param int $pageId
     *
     * @return Page|null
     */
    public function getNextPage(int $pageId)
    {
        $result = null;
        $pick = false;
        /** @var Page $page */
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

    /**
     * @param int $pageId
     *
     * @return bool
     */
    public function isFirstPage(int $pageId)
    {
        return $pageId === $this->pages->first()->getPageId();
    }

    /**
     * @return Config
     */
    public function getConfig():? Config
    {
        return $this->config;
    }

    /**
     * @param Config $config
     *
     * @return Survey
     */
    public function setConfig(Config $config): Survey
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getHiddenValues()
    {
        return $this->hiddenValues;
    }

    /**
     * @param $hiddenValues
     *
     * @return Survey
     */
    public function setHiddenValues($hiddenValues): Survey
    {
        $this->hiddenValues = $hiddenValues;

        return $this;
    }

}
