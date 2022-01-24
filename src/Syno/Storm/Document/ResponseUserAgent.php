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
    private $ipAddress;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $userAgent;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date")
     */
    private $createdAt;

    public function __construct(string $ipAddress, string $userAgent)
    {
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->createdAt = new \DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'ipAddress' => $this->getIpAddress(),
            'userAgent' => $this->getUserAgent(),
            'createdAt' => $this->getCreatedAt()->getTimestamp()
        ];
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
