<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;
use Syno\Storm\Traits\TranslationTrait;

/**
 * @ODM\EmbeddedDocument
 */
class QuestionTranslation implements JsonSerializable
{
    use TranslationTrait;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $text;

    public function jsonSerialize(): array
    {
        return [
            'locale' => $this->locale,
            'text'   => $this->text
        ];
    }

    public function getText():? string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

}
