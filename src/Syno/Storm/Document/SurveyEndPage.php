<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class SurveyEndPage implements JsonSerializable
{
    public const TYPE_SCREENOUT         = 'screenout';
    public const TYPE_QUALITY_SCREENOUT = 'quality_screenout';
    public const TYPE_COMPLETE          = 'complete';
    public const TYPE_QUOTA_FULL        = 'quota_full';

    /**
     * @ODM\Field(type="string")
     */
    private $language;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $type;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $content;

    public function jsonSerialize(): array
    {
        return [
            'language' => $this->language,
            'type'     => $this->type,
            'content'  => $this->content,
        ];
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language): void
    {
        $this->language = $language;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
