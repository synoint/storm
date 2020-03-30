<?php
declare(strict_types=1);

namespace Syno\Storm\Traits;

trait TranslationTrait
{
    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $locale;

    /**
     * @return string|null
     */
    public function getLocale():? string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }
}
