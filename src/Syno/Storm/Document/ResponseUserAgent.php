<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class ResponseUserAgent implements JsonSerializable
{
    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    public $ipAddress;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    public $userAgent;

    /**
     * @ODM\Field(type="date")
     */
    public $createdAt;

    /**
     * @param string    $ipAddress
     * @param string    $userAgent
     */
    public function __construct(string $ipAddress, string $userAgent)
    {
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->createdAt = new \DateTime();
    }

    public function jsonSerialize()
    {
        return [
            'ipAddress' => $this->ipAddress,
            'userAgent' => $this->userAgent,
            'createdAt' => $this->createdAt
        ];
    }


}
