<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class RandomizationBlock implements JsonSerializable
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
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $position;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $weight;

    /**
     * @var array
     *
     * @ODM\EmbedMany(targetDocument=BlockWeight::class)
     */
    private $items;

    public function jsonSerialize(): array
    {
        return [
            'id'       => $this->id,
            'type'     => $this->type,
            'position' => $this->position,
            'weight' => $this->weight,
            'items'    => $this->items,
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

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
}
