<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="response"))
 */
class Response
{
    /** @ODM\Id */
    private $id;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $responseId;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $surveyId;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $surveyVersion;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $pageId;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $mode;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $locale;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean")
     */
    private $completed = false;

    /**
     * @ODM\Field(type="timestamp")
     */
    private $createdAt;

    /** @ODM\EmbedMany(targetDocument=ResponseUserAgent::class) */
    private $userAgents;

    /** @ODM\EmbedMany(targetDocument=HiddenValue::class) */
    private $hiddenValues;

    /**
     * @param string $responseId
     *
     * @throws \Exception
     */
    public function __construct(string $responseId)
    {
        $this->responseId   = $responseId;
        $this->userAgents   = new ArrayCollection();
        $this->hiddenValues = new ArrayCollection();
        $this->createdAt    = time();
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
     * @return Response
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getResponseId(): string
    {
        return $this->responseId;
    }

    /**
     * @param string $responseId
     *
     * @return Response
     */
    public function setResponseId(string $responseId): Response
    {
        $this->responseId = $responseId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSurveyId(): int
    {
        return $this->surveyId;
    }

    /**
     * @param int $surveyId
     *
     * @return Response
     */
    public function setSurveyId(int $surveyId): Response
    {
        $this->surveyId = $surveyId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSurveyVersion(): int
    {
        return $this->surveyVersion;
    }

    /**
     * @param int $surveyVersion
     *
     * @return Response
     */
    public function setSurveyVersion(int $surveyVersion): Response
    {
        $this->surveyVersion = $surveyVersion;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }

    /**
     * @param int $pageId
     *
     * @return Response
     */
    public function setPageId(int $pageId): Response
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     *
     * @return Response
     */
    public function setMode(string $mode): Response
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return Response
     */
    public function setLocale(string $locale): Response
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     *
     * @return Response
     */
    public function setCompleted(bool $completed): Response
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return ArrayCollection
     */
    public function getUserAgents(): ArrayCollection
    {
        return $this->userAgents;
    }

    /**
     * @param ArrayCollection $userAgents
     *
     * @return Response
     */
    public function setUserAgents(ArrayCollection $userAgents): Response
    {
        $this->userAgents = $userAgents;

        return $this;
    }

    /**
     * @param string $ipAddress
     * @param string $userAgentString
     */
    public function addUserAgent(string $ipAddress, string $userAgentString)
    {
        if (!$this->userAgentExists($ipAddress, $userAgentString)) {
            $userAgent = new ResponseUserAgent($ipAddress, $userAgentString);
            $this->userAgents[] = $userAgent;
        }
    }

    /**
     * @param string $ipAddress
     * @param string $userAgentString
     *
     * @return bool
     */
    private function userAgentExists(string $ipAddress, string $userAgentString)
    {
        /** @var ResponseUserAgent $userAgent */
        foreach ($this->userAgents as $userAgent) {
            if ($userAgent->ipAddress === $ipAddress && $userAgent->userAgent === $userAgentString) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection
     */
    public function getHiddenValues()
    {
        return $this->hiddenValues;
    }

    /**
     * @param Collection $hiddenValues
     *
     * @return Response
     */
    public function setHiddenValues($hiddenValues): self
    {
        $this->hiddenValues = $hiddenValues;

        return $this;
    }

    /**
     * @param HiddenValue $hiddenValue
     */
    public function addHiddenValue(HiddenValue $hiddenValue)
    {
        if (!$this->hiddenValues->contains($hiddenValue)) {
            $this->hiddenValues[] = $hiddenValue;
        }
    }
}
