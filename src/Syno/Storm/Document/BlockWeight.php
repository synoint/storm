<?php

namespace Syno\Storm\Document;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class BlockWeight implements JsonSerializable
{
    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $id;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $block;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $page;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $question;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $answer;

    /**
     * @ODM\Field(type="string")
     */
    private $name;

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

    public function jsonSerialize(): array
    {
        return [
            'id'       => $this->id,
            'block'    => $this->block,
            'page'     => $this->page,
            'question' => $this->question,
            'answer'   => $this->answer,
            'name'     => $this->name,
            'position' => $this->position,
            'weight'   => $this->weight
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

    public function getBlock(): int
    {
        return $this->block;
    }

    public function setBlock(int $block): self
    {
        $this->block = $block;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getQuestion(): int
    {
        return $this->question;
    }

    public function setQuestion(int $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): int
    {
        return $this->answer;
    }

    public function setAnswer(int $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

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
}
