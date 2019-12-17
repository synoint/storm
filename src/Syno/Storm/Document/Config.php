<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class Config implements JsonSerializable
{
    /**
     * @var bool
     *
     * @ODM\Field(type="boolean")
     */
    public $privacyConsentEnabled;

    /**
     * @ODM\Field(type="string")
     */
    public $theme = 'materialize';

    public function jsonSerialize()
    {
        return [
            'privacyConsentEnabled' => $this->privacyConsentEnabled,
            'theme'                 => $this->theme
        ];
    }
}
