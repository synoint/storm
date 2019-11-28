<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\Document(repositoryClass="Syno\Storm\Repository\Survey", collection="survey"))
 */
class Survey
{
    /** @ODM\Id */
    private $id;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $stormMakerSurveyId;

    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    private $slug;

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
     * @ODM\ReferenceMany(targetDocument="Page")
     */
    private $pages;

    /**
     * Survey constructor.
     */
    public function __construct()
    {
        $this->pages = new ArrayCollection();
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
    public function getStormMakerSurveyId(): int
    {
        return $this->stormMakerSurveyId;
    }

    /**
     * @param int $stormMakerSurveyId
     *
     * @return Survey
     */
    public function setStormMakerSurveyId(int $stormMakerSurveyId): Survey
    {
        $this->stormMakerSurveyId = $stormMakerSurveyId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     *
     * @return Survey
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion(): int
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
     * @return Collection
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    /**
     * @param Collection $pages
     *
     * @return Survey
     */
    public function setPages(Collection $pages): Survey
    {
        $this->pages = $pages;

        return $this;
    }


}
