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
    const MODE_LIVE    = 'live';
    const MODE_TEST    = 'test';
    const MODE_DEBUG   = 'debug';

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
    private $pageCode;

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
     * @ODM\Field(type="bool")
     */
    private $completed = false;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     */
    private $screenedOut = false;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     */
    private $qualityScreenedOut = false;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     */
    private $quotaFull = false;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
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
     * @var array
     */
    private $events;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $surveyPathId;

    /**
     * @ODM\Field(type="date")
     */
    private $deletedAt;

    public function __construct(string $responseId)
    {
        $this->responseId = $responseId;
        $this->userAgents = new ArrayCollection();
        $this->parameters = new ArrayCollection();
        $this->answers    = new ArrayCollection();
        $this->createdAt  = new \DateTime();
        $this->events     = [];
    }

    public function jsonSerialize(): array
    {
        return [
            'id'                 => $this->id,
            'responseId'         => $this->responseId,
            'surveyId'           => $this->surveyId,
            'surveyVersion'      => $this->surveyVersion,
            'pageId'             => $this->pageId,
            'pageCode'           => $this->pageCode,
            'mode'               => $this->mode,
            'locale'             => $this->locale,
            'completed'          => $this->completed,
            'screenedOut'        => $this->screenedOut,
            'qualityScreenedOut' => $this->qualityScreenedOut,
            'quotaFull'          => $this->quotaFull,
            'screenoutId'        => $this->screenoutId,
            'createdAt'          => $this->createdAt->getTimestamp(),
            'completedAt'        => $this->completedAt,
            'deletedAt'          => $this->deletedAt,
            'userAgents'         => $this->userAgents,
            'parameters'         => $this->parameters,
            'answers'            => $this->getAnswers(),
            'events'             => $this->events,
            'surveyPathId'       => $this->surveyPathId
        ];
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getResponseId(): string
    {
        return $this->responseId;
    }

    public function setResponseId(string $responseId): self
    {
        $this->responseId = $responseId;

        return $this;
    }

    public function getSurveyId(): int
    {
        return $this->surveyId;
    }

    public function setSurveyId(int $surveyId): self
    {
        $this->surveyId = $surveyId;

        return $this;
    }

    public function getSurveyVersion(): int
    {
        return $this->surveyVersion;
    }

    public function setSurveyVersion(int $surveyVersion): self
    {
        $this->surveyVersion = $surveyVersion;

        return $this;
    }

    public function getPageId(): ?int
    {
        return $this->pageId;
    }

    public function setPageId(?int $pageId = null): self
    {
        $this->pageId = $pageId;

        return $this;
    }

    public function getPageCode(): string
    {
        return $this->pageCode;
    }

    public function setPageCode(string $pageCode): self
    {
        $this->pageCode = $pageCode;

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function isDebug(): bool
    {
        return self::MODE_DEBUG === $this->mode;
    }

    public function isTest(): bool
    {
        return self::MODE_TEST === $this->mode;
    }

    public function isLive(): bool
    {
        return self::MODE_LIVE === $this->mode;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    public function isScreenedOut(): bool
    {
        return $this->screenedOut;
    }

    public function setScreenedOut(bool $screenedOut): self
    {
        $this->screenedOut = $screenedOut;

        return $this;
    }

    public function isQualityScreenedOut(): bool
    {
        return $this->qualityScreenedOut;
    }

    public function setQualityScreenedOut(bool $qualityScreenedOut): self
    {
        $this->qualityScreenedOut = $qualityScreenedOut;

        return $this;
    }

    public function isQuotaFull(): bool
    {
        return $this->quotaFull;
    }

    public function setQuotaFull(bool $quotaFull): self
    {
        $this->quotaFull = $quotaFull;

        return $this;
    }

    public function getScreenoutId(): ?int
    {
        return $this->screenoutId;
    }

    public function setScreenoutId($screenoutId)
    {
        $this->screenoutId = $screenoutId;

        return $this;
    }

    public function isDone(): bool
    {
        return $this->isScreenedOut() || $this->isQualityScreenedOut() || $this->isQuotaFull() || $this->isCompleted();
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCompletedAt(int $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * @return Collection|ResponseUserAgent[]
     */
    public function getUserAgents(): Collection
    {
        return $this->userAgents;
    }

    public function setUserAgents(Collection $userAgents): self
    {
        $this->userAgents = $userAgents;

        return $this;
    }

    public function addUserAgent(string $ipAddress, string $userAgentString)
    {
        if (!$this->userAgentExists($ipAddress, $userAgentString)) {
            $userAgent          = new ResponseUserAgent($ipAddress, $userAgentString);
            $this->userAgents[] = $userAgent;
        }
    }

    private function userAgentExists(string $ipAddress, string $userAgentString): bool
    {
        /** @var ResponseUserAgent $userAgent */
        foreach ($this->userAgents as $userAgent) {
            if ($userAgent->getIpAddress() === $ipAddress && $userAgent->getUserAgent() === $userAgentString) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection|Parameter[]
     */
    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    public function getParameter(string $code): ?Parameter
    {
        return $this->parameters->filter(function (Parameter $parameter) use ($code) {
            return $parameter->getCode() == $code;
        })->current();
    }

    public function getSource(): ?int
    {
        foreach ($this->parameters as $parameter) {
            if ($parameter->getCode() == Parameter::PARAM_SOURCE) {
                return $parameter->getValue();
            }
        }

        return null;
    }

    public function setParameters(Collection $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function addParameter(Parameter $parameter)
    {
        if (!$this->parameters->contains($parameter)) {
            $this->parameters[] = $parameter;
        }
    }

    /**
     * @return Collection|ResponseAnswer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function saveAnswers(Collection $answers)
    {
        $questionIds = [];
        /** @var ResponseAnswer $newAnswer */
        foreach ($answers as $newAnswer) {
            $questionIds[] = $newAnswer->getQuestionId();
        }

        /** @var ResponseAnswer $existingAnswer */
        foreach ($this->answers as $existingAnswer) {
            if (in_array($existingAnswer->getQuestionId(), $questionIds)) {
                $this->answers->removeElement($existingAnswer);
            }
        }

        /** @var ResponseAnswer $newAnswer */
        foreach ($answers as $newAnswer) {
            $this->answers[] = $newAnswer;
        }
    }

    public function clearAnswers(): void
    {
        $this->answers->clear();
    }

    public function getAnswerIdMap(): array
    {
        $result = [];
        foreach ($this->getAnswers() as $responseAnswer) {
            foreach ($responseAnswer->getAnswers() as $answer) {
                $result[$answer->getAnswerId()] = 1;
            }
        }

        return $result;
    }

    public function getAnswerIdValueMap(): array
    {
        $result = [];

        foreach ($this->getAnswers() as $responseAnswer) {
            foreach ($responseAnswer->getAnswers() as $answer) {
                /**@var ResponseAnswerValue $answer */
                $result[$responseAnswer->getQuestionId()][$answer->getAnswerId()] = $answer->getValue();
            }
        }

        return $result;
    }

    public function setEvents(array $events): self
    {
        $this->events = $events;

        return $this;
    }

    public function getSurveyPathId(): ?string
    {
        return $this->surveyPathId;
    }

    public function setSurveyPathId(string $surveyPathId): self
    {
        $this->surveyPathId = $surveyPathId;

        return $this;
    }

    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function getCompletedAt(): int
    {
        return $this->completedAt;
    }

    public function getNumberOfAnsweredQuestions(): int
    {
        $questions = [];
        foreach ($this->answers as $responseAnswer) {
            $questions[$responseAnswer->getQuestionId()] = 1;
        }

        return count($questions);
    }
}
