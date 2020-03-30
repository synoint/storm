<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;
use Syno\Storm\Traits\TranslationTrait;

/**
 * @ODM\EmbeddedDocument
 */
class PageTranslation implements JsonSerializable
{
    use TranslationTrait;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $content;

    public function jsonSerialize()
    {
        return [
            'content' => $this->content
        ];
    }

    /**
     * @return string|null
     */
    public function getContent():? string
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
