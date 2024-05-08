<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class Config implements JsonSerializable
{
    const DEFAULT_THEME       = 'b4';
    const DEFAULT_COLOR_THEME = 'default';

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     */
    private $debugMode = false;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $debugToken;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     */
    private $privacyConsentEnabled = false;

    /**
     * @ODM\Field(type="string")
     */
    private $theme;

    /**
     * @ODM\Field(type="string")
     */
    private $colorTheme = 'default';

    /**
     * @ODM\Field(type="string")
     */
    private $cintDemandApiKey;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     */
    private $backButtonEnabled = true;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $profilingSurveyCallbackUrl = null;

    public function __construct()
    {
        $this->theme      = self::DEFAULT_THEME;
        $this->colorTheme = self::DEFAULT_COLOR_THEME;
    }

    public function jsonSerialize(): array
    {
        return [
            'debugMode'                  => $this->isDebugMode(),
            'debugToken'                 => $this->getDebugToken(),
            'privacyConsentEnabled'      => $this->isPrivacyConsentEnabled(),
            'theme'                      => $this->getTheme(),
            'colorTheme'                 => $this->getColorTheme(),
            'cintDemandApiKey'           => $this->getCintDemandApiKey(),
            'backButtonEnabled'          => $this->isBackButtonEnabled(),
            'profilingSurveyCallbackUrl' => $this->getProfilingSurveyCallbackUrl()
        ];
    }

    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    public function setDebugMode(bool $debugMode): self
    {
        $this->debugMode = $debugMode;

        return $this;
    }

    public function getDebugToken(): ?string
    {
        return $this->debugToken;
    }

    public function setDebugToken(?string $debugToken): self
    {
        $this->debugToken = $debugToken;

        return $this;
    }

    public function isPrivacyConsentEnabled(): bool
    {
        //Temporary show consent for all surveys
        return true; //$this->privacyConsentEnabled;
    }

    public function setPrivacyConsentEnabled(bool $privacyConsentEnabled): self
    {
        $this->privacyConsentEnabled = $privacyConsentEnabled;

        return $this;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getColorTheme(): string
    {
        return $this->colorTheme;
    }

    public function setColorTheme(string $colorTheme): self
    {
        $this->colorTheme = $colorTheme;

        return $this;
    }

    public function getCintDemandApiKey()
    {
        return $this->cintDemandApiKey;
    }

    public function setCintDemandApiKey($cintDemandApiKey)
    {
        $this->cintDemandApiKey = $cintDemandApiKey;

        return $this;
    }

    public function isBackButtonEnabled(): bool
    {
        return $this->backButtonEnabled;
    }

    public function setBackButtonEnabled(bool $backButtonEnabled): self
    {
        $this->backButtonEnabled = $backButtonEnabled;

        return $this;
    }

    public function getProfilingSurveyCallbackUrl(): ?string
    {
        return $this->profilingSurveyCallbackUrl;
    }

    public function setProfilingSurveyCallbackUrl(?string $profilingSurveyCallbackUrl): void
    {
        $this->profilingSurveyCallbackUrl = $profilingSurveyCallbackUrl;
    }
}
