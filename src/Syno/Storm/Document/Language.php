<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class Language implements JsonSerializable
{
    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $locale;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $nativeName;

    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean")
     */
    private $primary = false;

    public function jsonSerialize()
    {
        return [
            'locale'     => $this->locale,
            'nativeName' => $this->nativeName,
            'primary'    => $this->primary
        ];
    }

    /**
     * @return string|null
     */
    public function getLocale():? string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return self
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNativeName():? string
    {
        return $this->nativeName;
    }

    /**
     * @param string $nativeName
     *
     * @return self
     */
    public function setNativeName(string $nativeName): self
    {
        $this->nativeName = $nativeName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * @param bool $primary
     *
     * @return self
     */
    public function setPrimary(bool $primary): self
    {
        $this->primary = $primary;

        return $this;
    }
}
