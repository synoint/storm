<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class Config implements JsonSerializable
{
    const DEFAULT_THEME = 'b4';

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean")
     */
    public $debugMode = false;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    public $debugToken;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean")
     */
    public $privacyConsentEnabled;

    /**
     * @ODM\Field(type="string")
     */
    public $theme;

    public function __construct()
    {
        $this->theme = self::DEFAULT_THEME;
    }

    public function jsonSerialize()
    {
        return [
            'debugMode'             => $this->debugMode,
            'debugToken'            => $this->debugToken,
            'privacyConsentEnabled' => $this->privacyConsentEnabled,
            'theme'                 => $this->theme
        ];
    }
}
