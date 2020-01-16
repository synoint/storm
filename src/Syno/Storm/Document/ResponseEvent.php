<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="response_event"))
 */
class ResponseEvent
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
     * @var int
     *
     * @ODM\Field(type="int")
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
     * @param string $message
     * @param int    $responseId
     * @param int    $surveyId
     * @param int    $pageId
     */
    public function __construct(string $message, int $responseId, int $surveyId, int $pageId = null)
    {
        $this->time       = new \DateTime();
        $this->message    = $message;
        $this->responseId = $responseId;
        $this->surveyId   = $surveyId;
        $this->pageId     = $pageId;
    }
}
