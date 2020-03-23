<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class SurveyUrl implements JsonSerializable
{
    const TYPE_SCREENOUT            = 'screenout';
    const TYPE_QUALITY_SCREENOUT    = 'quality_screenout';
    const TYPE_COMPLETE             = 'complete';
    const TYPE_QUOTA_FULL           = 'quota_full';

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
