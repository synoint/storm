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
    public $type;

    /**
     * @var string
     *
     * @ODM\Field(type="integer")
     */
    public $source;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    public $url;

    public function jsonSerialize()
    {
        return [
            'type'   => $this->type,
            'source' => $this->source,
            'url'    => $this->url
        ];
    }
}
