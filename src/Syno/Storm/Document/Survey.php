<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;
use Syno\Storm\Traits\TranslatableTrait;

/**
 * @ODM\Document(collection="survey"))
 * @ODM\UniqueIndex(keys={"surveyId"="asc", "version"="asc"})
 */
class Survey implements JsonSerializable
{
    use TranslatableTrait;

    const URL_TYPE_SCREENOUT = 'screenout';
    const URL_TYPE_QUALITY_SCREENOUT = 'quality_screenout';
    const URL_TYPE_COMPLETE = 'complete';
    const URL_TYPE_QUOTA_FULL = 'quota_full';

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
    private $logoPath;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @Assert\Positive
     */
    private $version;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $publicTitle;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @Assert\NotNull
     */
    private $published = false;

    /**
     * @var Collection
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
     * @ODM\EmbedMany(targetDocument=Parameter::class)
     */
    private $parameters;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=SurveyUrl::class)
     */
    private $urls;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=Language::class)
     */
    private $languages;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=Css::class)
     */
    private $css;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=SurveyTranslation::class)
     */
    protected $translations;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=Randomization::class)
     */
    private $randomization;

    /**
     * @var SurveyCompleteCondition
     *
     * @ODM\EmbedOne(targetDocument=SurveyCompleteCondition::class)
     */
    private $surveyCompleteCondition;

    /**
     * @var SurveyScreenoutCondition
     *
     * @ODM\EmbedOne(targetDocument=SurveyScreenoutCondition::class)
     */
    private $surveyScreenoutCondition;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=SurveyEndPage::class)
     */
    private $endPages;

    /**
     * @var ?string
     *
     * @ODM\Field(type="string")
     */
    private $completeCallbackUrl;

    public function __construct()
    {
        $this->pages         = new ArrayCollection();
        $this->parameters    = new ArrayCollection();
        $this->urls          = new ArrayCollection();
        $this->languages     = new ArrayCollection();
        $this->css           = new ArrayCollection();
        $this->translations  = new ArrayCollection();
        $this->randomization = new ArrayCollection();
        $this->endPages      = new ArrayCollection();
    }

    public function jsonSerialize(): array
    {
        return [
            'id'                       => $this->id,
            'surveyId'                 => $this->surveyId,
            'logoPath'                 => $this->logoPath,
            'version'                  => $this->version,
            'languages'                => $this->languages,
            'translations'             => $this->translations,
            'surveyCompleteCondition'  => $this->surveyCompleteCondition,
            'surveyScreenoutCondition' => $this->surveyScreenoutCondition,
            'published'                => $this->published,
            'config'                   => $this->config,
            'parameters'               => $this->parameters,
            'urls'                     => $this->urls,
            'pages'                    => $this->pages,
            'css'                      => $this->css,
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

    public function getSurveyId(): ?int
    {
        return $this->surveyId;
    }

    public function setSurveyId(int $surveyId): self
    {
        $this->surveyId = $surveyId;

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

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return Collection|Page[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function getPlainPages(): array
    {
        $list = [];

        foreach ($this->pages as $page) {
            $list[] = (int) $page->getPageId();
        }

        return $list;
    }

    public function getFirstPage(): ?Page
    {
        $pages = $this->pages->filter(function (Page $page) {
            return $page->getQuestions()->count() == 0 || $page->getVisibleQuestions()->count() > 0;
        });

        if(!$pages->count()) {
            return null;
        }

        return $pages->first();
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

    public function getPageByQuestion(int $questionId): ?Page
    {
        $result = null;
        foreach ($this->pages as $page) {
            foreach ($page->getQuestions() as $question) {
                if ($questionId === $question->getQuestionId()) {
                    $result = $page;
                    break;
                }
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

    public function getConfig(): ?Config
    {
        return $this->config;
    }

    public function setConfig(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    public function setParameters($parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getUrls(): Collection
    {
        return $this->urls;
    }

    public function getCompleteUrl(?int $source): ?string
    {
        foreach ($this->getUrls() as $url) {
            /**@var SurveyUrl $url */
            if ($url->getSource() == $source && $url->getType() == self::URL_TYPE_COMPLETE) {
                return $url->getUrl();
            }
        }

        return null;
    }

    public function getScreenoutUrl(?int $source): ?string
    {
        foreach ($this->getUrls() as $url) {
            /**@var SurveyUrl $url */
            if ($url->getSource() == $source && $url->getType() == self::URL_TYPE_SCREENOUT) {
                return $url->getUrl();
            }
        }

        return null;
    }

    public function getQualityScreenoutUrl(?int $source): ?string
    {
        foreach ($this->getUrls() as $url) {
            /**@var SurveyUrl $url */
            if ($url->getSource() == $source && $url->getType() == self::URL_TYPE_QUALITY_SCREENOUT) {
                return $url->getUrl();
            }
        }

        return null;
    }

    public function setUrls($urls): self
    {
        $this->urls = $urls;

        return $this;
    }

    /**
     * @return Collection|Language[]
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function setLanguages(Collection $languages): self
    {
        $this->languages = $languages;

        return $this;
    }

    public function getPrimaryLanguageLocale(): ?string
    {
        foreach ($this->languages as $language) {
            if ($language->isPrimary()) {

                return $language->getLocale();
            }
        }

        return null;
    }

    public function getPublicTitle(): ?string
    {
        /** @var SurveyTranslation $translation */
        $translation = $this->getTranslation();
        if (null !== $translation && !empty($translation->getPublicTitle())) {
            return $translation->getPublicTitle();
        }

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

    /**
     * @return Collection|Randomization[]
     */
    public function getRandomization(): Collection
    {
        return $this->randomization;
    }

    public function setRandomization(Collection $randomization): self
    {
        $this->randomization = $randomization;

        return $this;
    }

    public function isRandomizationOn(): bool
    {
        return $this->randomization->count();
    }

    public function isFirstPage(int $pageId): bool
    {
        return $pageId === $this->pages->first()->getPageId();
    }

    public function getSurveyCompleteCondition(): ?SurveyCompleteCondition
    {
        return $this->surveyCompleteCondition;
    }

    public function setSurveyCompleteCondition(SurveyCompleteCondition $surveyCompleteCondition): self
    {
        $this->surveyCompleteCondition = $surveyCompleteCondition;

        return $this;
    }

    public function getSurveyScreenoutCondition(): ?SurveyScreenoutCondition
    {
        return $this->surveyScreenoutCondition;
    }

    public function setSurveyScreenoutCondition(SurveyScreenoutCondition $surveyScreenoutCondition): self
    {
        $this->surveyScreenoutCondition = $surveyScreenoutCondition;

        return $this;
    }

    public function getEndPages()
    {
        return $this->endPages;
    }

    public function setEndPages($endPages): void
    {
        $this->endPages = $endPages;
    }

    public function getCompleteCallbackUrl(): ?string
    {
        return $this->completeCallbackUrl;
    }

    public function setCompleteCallbackUrl(?string $completeCallbackUrl): void
    {
        $this->completeCallbackUrl = $completeCallbackUrl;
    }
}
