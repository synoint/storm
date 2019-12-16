<?php

namespace App\Tests\Unit\Syno\Storm\Document;

use App\Tests\Unit\TestCase;
use Syno\Storm\Document\Survey;

class SurveyTest extends TestCase
{
    /** @var Survey */
    private $document;

    public function setUp()
    {
        $this->document = new Survey();
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

    public function testGetSlug()
    {
        $this->document->setSlug('slug');
        $this->assertEquals('slug', $this->document->getSlug());
    }
}
