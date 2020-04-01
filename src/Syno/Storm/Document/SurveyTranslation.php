<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;
use Syno\Storm\Traits\TranslationTrait;

/**
 * @ODM\EmbeddedDocument
 */
class SurveyTranslation implements JsonSerializable
{
    use TranslationTrait;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $publicTitle;

    public function jsonSerialize()
    {
        return [
            'publicTitle' => $this->publicTitle
        ];
    }

    /**
     * @return string|null
     */
    public function getPublicTitle():? string
    {
        return $this->publicTitle;
    }

    /**
     * @param string $publicTitle
     *
     * @return self
     */
    public function setPublicTitle(string $publicTitle): self
    {
        $this->publicTitle = $publicTitle;

        return $this;
    }
}
