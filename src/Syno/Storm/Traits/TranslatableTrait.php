<?php

declare(strict_types=1);

namespace Syno\Storm\Traits;

use Doctrine\Common\Collections\Collection;
use Syno\Storm\Document\TranslationInterface;

trait TranslatableTrait
{
    /** @var Collection */
    protected $translations;

    /** @var string */
    protected $currentLocale;

    /** @var string */
    protected $fallbackLocale = 'en';

    /**
     * @return TranslationInterface|null
     */
    public function getTranslation()
    {
        if (null !== $this->currentLocale) {
            foreach ($this->translations as $translation) {
                if ($translation->getLocale() === $this->currentLocale) {
                    return $translation;
                }
            }
        }

        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $this->fallbackLocale) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * @return Collection|TranslationInterface[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param Collection $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @param string $currentLocale
     */
    public function setCurrentLocale(string $currentLocale)
    {
        $this->currentLocale = $currentLocale;
    }

    /**
     * @param string $fallbackLocale
     */
    public function setFallbackLocale(string $fallbackLocale)
    {
        $this->fallbackLocale = $fallbackLocale;
    }
}
