<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class HiddenValue implements JsonSerializable
{
    const TYPE_INT    = 'INT';
    const TYPE_STRING = 'STRING';

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    public $name;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    public $code;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    public $urlParam;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    public $type;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    public $value;

    public function jsonSerialize()
    {
        return [
            'name'     => $this->name,
            'code'     => $this->code,
            'urlParam' => $this->urlParam,
            'type'     => $this->type,
            'value'    => $this->value
        ];
    }
}
