<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;
/**
 * @ODM\Document(collection="response_event"))
 * @ODM\Index(keys={"responseId"="desc"})
 * @ODM\Index(keys={"surveyId"="desc"})
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
    private $pageId;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $message;

    /**
     * @var Collection
     *
     * @ODM\EmbedMany(targetDocument=ResponseAnswer::class)
     */
    private $answers;

    public function __construct(
        string $message,
        string $responseId,
        int $surveyId,
        int $pageId = null,
        Collection $answers = null
    )
    {
        $this->time       = new \DateTime();
        $this->message    = $message;
        $this->responseId = $responseId;
        $this->surveyId   = $surveyId;
        $this->pageId     = $pageId;
        $this->answers    = $answers;
    }


    public function jsonSerialize(): array
    {
        return [
            'id'         => $this->id,
            'time'       => $this->time->format('Y-m-d H:i:s.v'),
            'message'    => $this->message,
            'responseId' => $this->responseId,
            'surveyId'   => $this->surveyId,
            'pageId'     => $this->pageId,
            'answers'    => $this->answers
        ];
    }

    public function getTimestamp(): int
    {
        return $this->time->getTimestamp();
    }

    public function getResponseId(): string
    {
        return $this->responseId;
    }
}
