<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;
/**
 * @ODM\Document(collection="response_event"))
 */
class ResponseEvent implements JsonSerializable
{
    /** @ODM\Id */
    private $id;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date")
     */
    private $time;

    /**
     * @var string
     *
     * @ODM\Field(type="string") @ODM\Index
     */
    private $responseId;

    /**
     * @var int
     *
     * @ODM\Field(type="int") @ODM\Index
     */
    private $surveyId;

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
    private $message;

    public function __construct(string $message, string $responseId, int $surveyId, int $pageId = null)
    {
        $this->time       = new \DateTime();
        $this->message    = $message;
        $this->responseId = $responseId;
        $this->surveyId   = $surveyId;
        $this->pageId     = $pageId;
    }


    public function jsonSerialize()
    {
        return [
            'id'         => $this->id,
            'time'       => $this->getTimestamp(),
            'message'    => $this->message,
            'responseId' => $this->responseId,
            'surveyId'   => $this->surveyId,
            'pageId'     => $this->pageId,
        ];
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->time->getTimestamp();
    }

    /**
     * @return string
     */
    public function getResponseId(): string
    {
        return $this->responseId;
    }
}
