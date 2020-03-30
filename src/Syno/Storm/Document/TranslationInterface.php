<?php
namespace Syno\Storm\Document;

interface TranslationInterface
{
    /**
     * @return string|null
     */
    public function getLocale(): ?string;

    /**
     * @param string $locale
     */
    public function setLocale(string $locale);
}
