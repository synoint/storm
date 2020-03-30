<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;
use Syno\Storm\Traits\TranslationTrait;

/**
 * @ODM\EmbeddedDocument
 */
class AnswerTranslation implements JsonSerializable, TranslationInterface
{
    use TranslationTrait;

    /**
     * @var string|null
     *
     * @ODM\Field(type="string")
     */
    private $label;

    /**
     * @var string|null
     *
     * @ODM\Field(type="string")
     */
    private $rowLabel;

    /**
     * @var string|null
     *
     * @ODM\Field(type="string")
     */
    private $columnLabel;

    public function jsonSerialize()
    {
        return [
            'locale'      => $this->locale,
            'label'       => $this->label,
            'rowLabel'    => $this->rowLabel,
            'columnLabel' => $this->columnLabel
        ];
    }

    /**
     * @return string|null
     */
    public function getLabel():? string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return self
     */
    public function setLabel(string $label = null): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRowLabel():? string
    {
        return $this->rowLabel;
    }

    /**
     * @param string $rowLabel
     *
     * @return self
     */
    public function setRowLabel(string $rowLabel = null): self
    {
        $this->rowLabel = $rowLabel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getColumnLabel():? string
    {
        return $this->columnLabel;
    }

    /**
     * @param string $columnLabel
     *
     * @return self
     */
    public function setColumnLabel(string $columnLabel = null): self
    {
        $this->columnLabel = $columnLabel;

        return $this;
    }


}
