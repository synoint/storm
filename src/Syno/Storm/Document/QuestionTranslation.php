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

    public function jsonSerialize()
    {
        return [
            'text' => $this->text
        ];
    }

    /**
     * @return string|null
     */
    public function getText():? string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return self
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

}
