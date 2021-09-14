<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class Css implements JsonSerializable
{
    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     */
    public $default;

    /**
     * @ODM\Field(type="string")
     */
    public $language;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    public $css;

    public function jsonSerialize()
    {
        return [
            'default' => $this->default,
            'language' => $this->language,
            'css' => $this->css,
        ];
    }
}
