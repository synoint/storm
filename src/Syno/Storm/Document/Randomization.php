<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\Collection;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class Randomization implements JsonSerializable
{
    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $id;

    /**
     * @ODM\Field(type="string")
     */
    private $type;

    /**
     * @var array
     *
     * @ODM\EmbedMany(targetDocument=BlockItem::class)
     */
    private $items;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     */
    private $isRandomized;

    public function jsonSerialize(): array
    {
        return [
            'id'    => $this->id,
            'type'  => $this->type,
            'items' => $this->items,
            'isRandomized' => $this->isRandomized
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function isRandomized(): bool
    {
        return $this->isRandomized ?: false;
    }

    public function setIsRandomized(?bool $isRandomized): self
    {
        $this->isRandomized = $isRandomized;

        return $this;
    }
}
