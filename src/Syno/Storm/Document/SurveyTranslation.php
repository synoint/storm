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

    public function jsonSerialize(): array
    {
        return [
            'publicTitle' => $this->publicTitle
        ];
    }

    public function getPublicTitle():? string
    {
        return $this->publicTitle;
    }

    public function setPublicTitle(string $publicTitle): self
    {
        $this->publicTitle = $publicTitle;

        return $this;
    }
}
