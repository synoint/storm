<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

use JsonSerializable;
/**
 * @ODM\Document(collection="response"))
 * @ODM\UniqueIndex(keys={"surveyId"="asc", "responseId"="asc"})
 */
class Response implements JsonSerializable
{
    const MODE_LIVE  = 'live';
    const MODE_TEST  = 'test';
    const MODE_DEBUG = 'debug';

    const PARAM_SOURCE = 'SOURCE';

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
     * @var bool
     *
     * @ODM\Field(type="boolean")
     */
    private $screenedOut = false;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean")
     */
    private $qualityScreenedOut = false;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean")
     */
    private $quotaFull = false;

    /**
     * @var int
     *
     * @ODM\Field(type="integer")
     */
    private $screenoutId;

    /**
     * @ODM\Field(type="date")
     */
    private $createdAt;

    /**
     * @var int
     */
    private $completedAt;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=ResponseUserAgent::class)
     */
    private $userAgents;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=Parameter::class)
     */
    private $parameters;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=ResponseAnswer::class)
     */
    private $answers;

    /**
     * @param string $responseId
     *
     * @throws \Exception
     */
    public function __construct(string $responseId)
    {
        $this->responseId   = $responseId;
        $this->userAgents   = new ArrayCollection();
        $this->parameters = new ArrayCollection();
        $this->createdAt    = new \DateTime();
    }

    public function jsonSerialize()
    {
        return [
            'responseId'            => $this->responseId,
            'surveyId'              => $this->surveyId,
            'surveyVersion'         => $this->surveyVersion,
            'pageId'                => $this->pageId,
            'mode'                  => $this->mode,
            'locale'                => $this->locale,
            'completed'             => $this->completed,
            'screenedOut'           => $this->screenedOut,
            'qualityScreenedOut'    => $this->qualityScreenedOut,
            'quotaFull'             => $this->quotaFull,
            'screenoutId'           => $this->screenoutId,
            'createdAt'             => $this->createdAt->getTimestamp(),
            'completedAt'           => $this->completedAt,
            'userAgents'            => $this->userAgents,
            'parameters'          => $this->parameters,
            'answers'               => $this->answers
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
     * @return self
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
     * @return self
     */
    public function setResponseId(string $responseId): self
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
     * @return self
     */
    public function setSurveyId(int $surveyId): self
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
     * @return self
     */
    public function setSurveyVersion(int $surveyVersion): self
    {
        $this->surveyVersion = $surveyVersion;

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
     * @param int|null $pageId
     *
     * @return self
     */
    public function setPageId(int $pageId = null): self
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
     * @return self
     */
    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return self::MODE_DEBUG === $this->mode;
    }

    /**
     * @return bool
     */
    public function isTest(): bool
    {
        return self::MODE_TEST === $this->mode;
    }

    /**
     * @return bool
     */
    public function isLive(): bool
    {
        return self::MODE_LIVE === $this->mode;
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
     * @return self
     */
    public function setLocale(string $locale): self
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
     * @return self
     */
    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isScreenedOut(): bool
    {
        return $this->screenedOut;
    }

    /**
     * @param bool $screenedOut
     *
     * @return self
     */
    public function setScreenedOut(bool $screenedOut): self
    {
        $this->screenedOut = $screenedOut;

        return $this;
    }

    /**
     * @return bool
     */
    public function isQualityScreenedOut(): bool
    {
        return $this->qualityScreenedOut;
    }

    /**
     * @param bool $qualityScreenedOut
     *
     * @return self
     */
    public function setQualityScreenedOut(bool $qualityScreenedOut): self
    {
        $this->qualityScreenedOut = $qualityScreenedOut;

        return $this;
    }

    /**
     * @return bool
     */
    public function isQuotaFull(): bool
    {
        return $this->quotaFull;
    }

    /**
     * @param bool $quotaFull
     *
     * @return self
     */
    public function setQuotaFull(bool $quotaFull): self
    {
        $this->quotaFull = $quotaFull;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getScreenoutId()
    {
        return $this->screenoutId;
    }

    /**
     * @param mixed $screenoutId
     *
     * @return self
     */
    public function setScreenoutId($screenoutId)
    {
        $this->screenoutId = $screenoutId;

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
     * @param int $completedAt
     *
     * @return self
     */
    public function setCompletedAt(int $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
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
     * @return self
     */
    public function setUserAgents(ArrayCollection $userAgents): self
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
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return null|int
     */
    public function getSource()
    {
        foreach ($this->parameters as $parameter) {
            if ($parameter->getCode() == self::PARAM_SOURCE) {
                return $parameter->getValue();
            }
        }

        return null;
    }

    /**
     * @param Collection $parameters
     *
     * @return self
     */
    public function setParameters($parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param Parameter $parameter
     */
    public function addParameter(Parameter $parameter)
    {
        if (!$this->parameters->contains($parameter)) {
            $this->parameters[] = $parameter;
        }
    }

    /**
     * @return Collection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param ResponseAnswer $responseAnswer
     */
    public function addAnswer(ResponseAnswer $responseAnswer)
    {
        $this->answers->set($responseAnswer->getQuestionId(), $responseAnswer);
    }

    public function clearAnswers()
    {
        $this->answers->clear();
    }

    public function getLastAnswersId()
    {
        $questionsId = [];
        $answersId = [];

        foreach ($this->getAnswers() as $responseAnswer) {
            /**@var ResponseAnswer $responseAnswer */
            $questionsId[$responseAnswer->getQuestionId()] = [];
            foreach ($responseAnswer->getAnswers() as $answer) {
                $questionsId[$responseAnswer->getQuestionId()][] = [$answer->getAnswerId() => 1];
            }
        }

        foreach ($questionsId as $questionId) {
            foreach ($questionId as $answers) {
                foreach ($answers as $answerId => $value) {
                    $answersId[$answerId] = $value;
                }
            }
        }

        return $answersId;
    }
}
