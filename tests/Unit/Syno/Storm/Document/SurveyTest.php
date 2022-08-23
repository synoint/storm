<?php

namespace App\Tests\Unit\Syno\Storm\Document;

use App\Tests\Traits\DocumentMockTrait;
use App\Tests\Unit\TestCase;
use Doctrine\Common\Collections;
use Doctrine\Common\Collections\ArrayCollection;
use Syno\Storm\Document;

class SurveyTest extends TestCase
{
    use DocumentMockTrait;

    private Document\Survey $document;

    public function setUp(): void
    {
        $this->document = new Document\Survey();
        $this->setHiddenProperty($this->document, 'id', 123);
    }

    public function testGetId()
    {
        $this->assertEquals(123, $this->document->getId());
    }

    public function testGetSurveyId()
    {
        $this->document->setSurveyId(123456789);
        $this->assertEquals(123456789, $this->document->getSurveyId());
    }

    public function testGetVersion()
    {
        $this->document->setVersion(2);
        $this->assertEquals(2, $this->document->getVersion());
    }

    public function testRandomizationIsArray()
    {
        $this->document->setRandomization(new ArrayCollection([$this->mockRandomization()]));

        $this->assertObjectEquals(Collections\ArrayCollection::class, $this->document->getRandomization());
    }
}
