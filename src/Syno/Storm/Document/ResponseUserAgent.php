<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class ResponseUserAgent
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
     * @ODM\Field(type="timestamp")
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
        $this->createdAt = time();
    }


}
