<?php
declare(strict_types=1);

namespace App\Tests\Unit\Syno\Storm\Services;

use App\Tests\Traits\DocumentMockTrait;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Syno\Storm\Document;
use Syno\Storm\Services;

final class RandomizationWeightTest extends TestCase
{
    use DocumentMockTrait;

    private Document\Survey              $survey;
    private Services\Permutation         $permutationService;
    private Services\RandomizationWeight $randomizationWeightService;

    public function setUp(): void
    {
        $this->permutationService         = new Services\Permutation();
        $this->randomizationWeightService = new Services\RandomizationWeight();

        $this->survey = $this->mockSurvey();

        $block1 = $this->mockRandomization(['id' => 1, 'type' => 'page']);
        $block2 = $this->mockRandomization(['id' => 2, 'type' => 'page']);
        $block3 = $this->mockRandomization(['id' => 3, 'type' => 'page']);
        $block4 = $this->mockRandomization(['id' => 4, 'type' => 'block', 'isRandomized' => true]);

        $blockItem1 = $this->mockBlockItem(['id' => 101, 'pageId' => 1001]);
        $blockItem2 = $this->mockBlockItem(['id' => 102, 'pageId' => 1002]);

        $blockItem3 = $this->mockBlockItem(['id' => 103, 'pageId' => 1003]);
        $blockItem4 = $this->mockBlockItem(['id' => 104, 'pageId' => 1004]);

        $blockItem5 = $this->mockBlockItem(['id' => 105, 'pageId' => 1003, 'isRandomized' => true, 'weight' => 1]);
        $blockItem6 = $this->mockBlockItem(['id' => 106, 'pageId' => 1004, 'isRandomized' => true, 'weight' => 2]);

        $blockItem7 = $this->mockBlockItem(['id' => 107, 'blockId' => 1, 'isRandomized' => true, 'weight' => 1]);
        $blockItem8 = $this->mockBlockItem(['id' => 108, 'blockId' => 2, 'isRandomized' => true, 'weight' => 1]);
        $blockItem9 = $this->mockBlockItem(['id' => 109, 'blockId' => 3, 'isRandomized' => true, 'weight' => 5]);

        $block1->setItems([$blockItem1, $blockItem2]);
        $block2->setItems([$blockItem3, $blockItem4]);
        $block3->setItems([$blockItem5, $blockItem6]);
        $block4->setItems([$blockItem7, $blockItem8, $blockItem9]);

        $this->survey->setRandomization(new ArrayCollection([$block1, $block2, $block3, $block4]));
    }

    public static function callMethod($obj, string $name, array $args)
    {
        $class  = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    public function testBlockWeights(): void
    {
        $weights = $this->randomizationWeightService->getBlockWeights($this->survey);

        // key is block id
        $this->assertEquals(14.29, $weights['1'], 'Error. Incorrectly returned weight');
        $this->assertEquals(14.29, $weights['2'], 'Error. Incorrectly returned weight');
        $this->assertEquals(71.45, $weights['3'], 'Error. Incorrectly returned weight');
    }

    public function testPageWeights(): void
    {
        $weights = $this->randomizationWeightService->getPageWeights($this->survey);

        // key is page id
        $this->assertEquals(33.33, $weights['1003'], 'Error. Incorrectly returned weight');
        $this->assertEquals(66.66, $weights['1004'], 'Error. Incorrectly returned weight');
    }
}
