<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class SurveyUrl implements JsonSerializable
{
    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $type;

    /**
     * @var string
     *
     * @ODM\Field(type="integer")
     */
    private $source;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $url;

    public function jsonSerialize()
    {
        return [
            'type'   => $this->type,
            'source' => $this->source,
            'url'    => $this->url
        ];
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param int $source
     *
     * @return self
     */
    public function setSource(int $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return self
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }
}
