<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class BlockItem implements JsonSerializable
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
     * @var int
     *
     * @ODM\Field(type="bool")
     */
    private $randomize;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $weight;

    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->id,
            'block'     => $this->block,
            'page'      => $this->page,
            'question'  => $this->question,
            'answer'    => $this->answer,
            'randomize' => $this->randomize,
            'weight'    => $this->weight
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

    public function getRandomize(): bool
    {
        return $this->randomize;
    }

    public function setRandomize(bool $randomize): self
    {
        $this->randomize = $randomize;

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
