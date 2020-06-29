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
    const DEFAULT_COLOR_THEME = 'default';

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

    /**
     * @ODM\Field(type="string")
     */
    public $colorTheme = 'default';

    public function __construct()
    {
        $this->theme      = self::DEFAULT_THEME;
        $this->colorTheme = self::DEFAULT_COLOR_THEME;
    }

    public function jsonSerialize()
    {
        return [
            'debugMode'             => $this->debugMode,
            'debugToken'            => $this->debugToken,
            'privacyConsentEnabled' => $this->privacyConsentEnabled,
            'theme'                 => $this->theme,
            'colorTheme'            => $this->colorTheme
        ];
    }
}
