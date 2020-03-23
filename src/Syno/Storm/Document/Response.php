<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="response"))
 * @ODM\UniqueIndex(keys={"surveyId"="asc", "responseId"="asc"})
 */
class Response
{
    const MODE_LIVE  = 'live';
    const MODE_TEST  = 'test';
    const MODE_DEBUG = 'debug';

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

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=ResponseUserAgent::class)
     */
    private $userAgents;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=HiddenValue::class)
     */
    private $hiddenValues;

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
    public function getPageId():? int
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
     * @param string $route
     *
     * @return Response
     */
    public function setModeByRoute(string $route): Response
    {
        switch ($route) {
            case 'survey.index':
                $this->mode = self::MODE_LIVE;
                break;
            case 'survey.test':
                $this->mode = self::MODE_TEST;
                break;
            case 'survey.debug':
                $this->mode = self::MODE_DEBUG;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown route: "%s"', $route));
        }

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
     * @return int
     */
    public function getCreatedAt(): int
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
     * @param string $urlParam
     *
     * @return HiddenValue
     */
    public function getHiddenValue(string $urlParam)
    {
        return $this->hiddenValues->filter(function(HiddenValue $hiddenValue) use ($urlParam) {
            return $hiddenValue->urlParam === $urlParam;
        })->current();
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
                $questionsId[$responseAnswer->getQuestionId()] = [$answer->getAnswerId() => 1];
            }
        }

        foreach ($questionsId as $questionId) {
            foreach ($questionId as $answerIds => $value) {
                $answersId[$answerIds] = $value;
            }
        }

        return $answersId;
    }
}
