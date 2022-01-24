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
    private $default;

    /**
     * @ODM\Field(type="string")
     */
    private $language;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $css;

    public function jsonSerialize(): array
    {
        return [
            'default'  => $this->default,
            'language' => $this->language,
            'css'      => $this->css,
        ];
    }

    public function isDefault(): bool
    {
        return (bool) $this->default;
    }

    public function setDefault(bool $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function getLanguage():? string
    {
        return $this->language;
    }

    public function setLanguage(string $language)
    {
        $this->language = $language;

        return $this;
    }

    public function getCss():? string
    {
        return $this->css;
    }

    public function setCss(string $css): self
    {
        $this->css = $css;

        return $this;
    }


}
