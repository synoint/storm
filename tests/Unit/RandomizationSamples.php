<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Tests\Traits\DocumentMockTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Syno\Storm\Document;

trait RandomizationSamples
{
    use DocumentMockTrait;

    /**
     * P1-3 & P4-7 are randomized - 720  combinations
     */
    public function sample1(): Document\Survey
    {
        $survey = $this->mockSurvey();

        $page1 = $this->mockPage(['id' => 1, 'pageId' => 1001, 'code' => 'P1']);
        $page2 = $this->mockPage(['id' => 2, 'pageId' => 1002, 'code' => 'P2']);
        $page3 = $this->mockPage(['id' => 3, 'pageId' => 1003, 'code' => 'P3']);
        $page4 = $this->mockPage(['id' => 4, 'pageId' => 1004, 'code' => 'P4']);
        $page5 = $this->mockPage(['id' => 5, 'pageId' => 1005, 'code' => 'P5']);
        $page6 = $this->mockPage(['id' => 6, 'pageId' => 1006, 'code' => 'P6']);
        $page7 = $this->mockPage(['id' => 7, 'pageId' => 1007, 'code' => 'P7']);

        $survey->setPages(new ArrayCollection([$page1, $page2, $page3, $page4, $page5, $page6, $page7]));

        $block1 = $this->mockRandomization(['id' => 1, 'type' => 'page', 'isRandomized' => true]);

        $blockItem1 = $this->mockBlockItem(['id' => 101, 'pageId' => 1001, 'isRandomized' => true, 'weight' => 1]);
        $blockItem2 = $this->mockBlockItem(['id' => 102, 'pageId' => 1002, 'isRandomized' => true, 'weight' => 1]);
        $blockItem3 = $this->mockBlockItem(['id' => 103, 'pageId' => 1003, 'isRandomized' => true, 'weight' => 1]);
        // IMPORTANT! P4 is not randomized
        $blockItem5 = $this->mockBlockItem(['id' => 105, 'pageId' => 1005, 'isRandomized' => true, 'weight' => 1]);
        $blockItem6 = $this->mockBlockItem(['id' => 106, 'pageId' => 1006, 'isRandomized' => true, 'weight' => 1]);
        $blockItem7 = $this->mockBlockItem(['id' => 107, 'pageId' => 1007, 'isRandomized' => true, 'weight' => 1]);

        $block1->setItems(new ArrayCollection([$blockItem1, $blockItem2, $blockItem3, $blockItem5, $blockItem6, $blockItem7]));

        $survey->setRandomization(new ArrayCollection([$block1]));

        return $survey;
    }

    /**
     * P5, P6 are randomized - 2 combinations
     * 3 blocks with 2 pages inside are randomized - 6 combinations
     * total of 12 combinations
     */
    public function sample2(): Document\Survey
    {
        $survey = $this->mockSurvey();

        $page1 = $this->mockPage(['id' => 1, 'pageId' => 1001, 'code' => 'P1']);
        $page2 = $this->mockPage(['id' => 2, 'pageId' => 1002, 'code' => 'P2']);
        $page3 = $this->mockPage(['id' => 3, 'pageId' => 1003, 'code' => 'P3']);
        $page4 = $this->mockPage(['id' => 4, 'pageId' => 1004, 'code' => 'P4']);
        $page5 = $this->mockPage(['id' => 5, 'pageId' => 1005, 'code' => 'P5']);
        $page6 = $this->mockPage(['id' => 6, 'pageId' => 1006, 'code' => 'P6']);
        $page7 = $this->mockPage(['id' => 7, 'pageId' => 1007, 'code' => 'P7']);

        $survey->setPages(new ArrayCollection([$page1, $page2, $page3, $page4, $page5, $page6, $page7]));

        $block1 = $this->mockRandomization(['id' => 1, 'type' => 'page']);
        $block2 = $this->mockRandomization(['id' => 2, 'type' => 'page']);
        $block3 = $this->mockRandomization(['id' => 3, 'type' => 'page', 'isRandomized' => true]);
        $block4 = $this->mockRandomization(['id' => 4, 'type' => 'block', 'isRandomized' => true]);

        $blockItem1 = $this->mockBlockItem(['id' => 101, 'pageId' => 1001]);
        $blockItem2 = $this->mockBlockItem(['id' => 102, 'pageId' => 1002]);

        $blockItem3 = $this->mockBlockItem(['id' => 103, 'pageId' => 1003]);
        $blockItem4 = $this->mockBlockItem(['id' => 104, 'pageId' => 1004]);

        $blockItem5 = $this->mockBlockItem(['id' => 105, 'pageId' => 1006, 'isRandomized' => true, 'weight' => 1]);
        $blockItem6 = $this->mockBlockItem(['id' => 106, 'pageId' => 1007, 'isRandomized' => true, 'weight' => 2]);

        $blockItem7 = $this->mockBlockItem(['id' => 107, 'blockId' => 1, 'isRandomized' => true, 'weight' => 1]);
        $blockItem8 = $this->mockBlockItem(['id' => 108, 'blockId' => 2, 'isRandomized' => true, 'weight' => 1]);
        $blockItem9 = $this->mockBlockItem(['id' => 109, 'blockId' => 3, 'isRandomized' => true, 'weight' => 5]);

        $block1->setItems(new ArrayCollection([$blockItem1, $blockItem2]));
        $block2->setItems(new ArrayCollection([$blockItem3, $blockItem4]));
        $block3->setItems(new ArrayCollection([$blockItem5, $blockItem6]));
        $block4->setItems(new ArrayCollection([$blockItem7, $blockItem8, $blockItem9]));

        $survey->setRandomization(new ArrayCollection([$block1, $block2, $block3, $block4]));

        return $survey;
    }

    /**
     * P1,P2 & P3, 4 & P5,P6 are randomized between each other, 6 combinations
     */
    public function sample3(): Document\Survey
    {
        $survey = $this->mockSurvey();

        $page1 = $this->mockPage(['id' => 1, 'pageId' => 1001, 'code' => 'P1']);
        $page2 = $this->mockPage(['id' => 2, 'pageId' => 1002, 'code' => 'P2']);
        $page3 = $this->mockPage(['id' => 3, 'pageId' => 1003, 'code' => 'P3']);
        $page4 = $this->mockPage(['id' => 4, 'pageId' => 1004, 'code' => 'P4']);
        $page5 = $this->mockPage(['id' => 5, 'pageId' => 1005, 'code' => 'P5']);
        $page6 = $this->mockPage(['id' => 6, 'pageId' => 1006, 'code' => 'P6']);

        $survey->setPages(new ArrayCollection([$page1, $page2, $page3, $page4, $page5, $page6]));

        $block1 = $this->mockRandomization(['id' => 1, 'type' => 'page']);
        $block2 = $this->mockRandomization(['id' => 2, 'type' => 'page']);
        $block3 = $this->mockRandomization(['id' => 3, 'type' => 'page']);
        $block4 = $this->mockRandomization(['id' => 4, 'type' => 'block', 'isRandomized' => true]);

        $blockItem1 = $this->mockBlockItem(['id' => 101, 'pageId' => 1001]);
        $blockItem2 = $this->mockBlockItem(['id' => 102, 'pageId' => 1002]);

        $blockItem3 = $this->mockBlockItem(['id' => 103, 'pageId' => 1003]);
        $blockItem4 = $this->mockBlockItem(['id' => 104, 'pageId' => 1004]);

        $blockItem5 = $this->mockBlockItem(['id' => 105, 'pageId' => 1005]);
        $blockItem6 = $this->mockBlockItem(['id' => 106, 'pageId' => 1006]);

        $blockItem7 = $this->mockBlockItem(['id' => 107, 'blockId' => 1, 'isRandomized' => true, 'weight' => 1]);
        $blockItem8 = $this->mockBlockItem(['id' => 108, 'blockId' => 2, 'isRandomized' => true, 'weight' => 1]);
        $blockItem9 = $this->mockBlockItem(['id' => 109, 'blockId' => 3, 'isRandomized' => true, 'weight' => 5]);

        $block1->setItems(new ArrayCollection([$blockItem1, $blockItem2]));
        $block2->setItems(new ArrayCollection([$blockItem3, $blockItem4]));
        $block3->setItems(new ArrayCollection([$blockItem5, $blockItem6]));
        $block4->setItems(new ArrayCollection([$blockItem7, $blockItem8, $blockItem9]));

        $survey->setRandomization(new ArrayCollection([$block1, $block2, $block3, $block4]));

        return $survey;
    }

    /**
     * P1 - P3, P4 - P7 randomize - 6 & 24 combinations
     * Total of 144
     */
    public function sample4(): Document\Survey
    {
        $survey = $this->mockSurvey();

        $page1 = $this->mockPage(['id' => 1, 'pageId' => 1001, 'code' => 'P1']);
        $page2 = $this->mockPage(['id' => 2, 'pageId' => 1002, 'code' => 'P2']);
        $page3 = $this->mockPage(['id' => 3, 'pageId' => 1003, 'code' => 'P3']);
        $page4 = $this->mockPage(['id' => 4, 'pageId' => 1004, 'code' => 'P4']);
        $page5 = $this->mockPage(['id' => 5, 'pageId' => 1005, 'code' => 'P5']);
        $page6 = $this->mockPage(['id' => 6, 'pageId' => 1006, 'code' => 'P6']);
        $page7 = $this->mockPage(['id' => 7, 'pageId' => 1007, 'code' => 'P7']);

        $survey->setPages(new ArrayCollection([$page1, $page2, $page3, $page4, $page5, $page6, $page7]));

        $block1 = $this->mockRandomization(['id' => 1, 'type' => 'page', 'isRandomized' => true]);
        $block2 = $this->mockRandomization(['id' => 2, 'type' => 'page', 'isRandomized' => true]);

        $blockItem1 = $this->mockBlockItem(['id' => 101, 'pageId' => 1001, 'isRandomized' => true, 'weight' => 1]);
        $blockItem2 = $this->mockBlockItem(['id' => 102, 'pageId' => 1002, 'isRandomized' => true, 'weight' => 1]);
        $blockItem3 = $this->mockBlockItem(['id' => 103, 'pageId' => 1003, 'isRandomized' => true, 'weight' => 1]);
        $blockItem4 = $this->mockBlockItem(['id' => 104, 'pageId' => 1004, 'isRandomized' => true, 'weight' => 1]);
        $blockItem5 = $this->mockBlockItem(['id' => 105, 'pageId' => 1005, 'isRandomized' => true, 'weight' => 1]);
        $blockItem6 = $this->mockBlockItem(['id' => 106, 'pageId' => 1006, 'isRandomized' => true, 'weight' => 1]);
        $blockItem7 = $this->mockBlockItem(['id' => 107, 'pageId' => 1007, 'isRandomized' => true, 'weight' => 1]);

        $block1->setItems(new ArrayCollection([$blockItem1, $blockItem2, $blockItem3]));
        $block2->setItems(new ArrayCollection([$blockItem4, $blockItem5, $blockItem6, $blockItem7]));

        $survey->setRandomization(new ArrayCollection([$block1, $block2]));

        return $survey;
    }

    /**
     * P1,P4,P7 randomized - 6 combinations
     * P2,P3 & P5,P6 randomized as blocks - 2 combinations
     * total of 12 combinations
     */
    public function sample5(): Document\Survey
    {
        $survey = $this->mockSurvey();

        $page1 = $this->mockPage(['id' => 1, 'pageId' => 1001, 'code' => 'P1']);
        $page2 = $this->mockPage(['id' => 2, 'pageId' => 1002, 'code' => 'P2']);
        $page3 = $this->mockPage(['id' => 3, 'pageId' => 1003, 'code' => 'P3']);
        $page4 = $this->mockPage(['id' => 4, 'pageId' => 1004, 'code' => 'P4']);
        $page5 = $this->mockPage(['id' => 5, 'pageId' => 1005, 'code' => 'P5']);
        $page6 = $this->mockPage(['id' => 6, 'pageId' => 1006, 'code' => 'P6']);
        $page7 = $this->mockPage(['id' => 7, 'pageId' => 1007, 'code' => 'P7']);

        $survey->setPages(new ArrayCollection([$page1, $page2, $page3, $page4, $page5, $page6, $page7]));

        $block1 = $this->mockRandomization(['id' => 1, 'type' => 'page', 'isRandomized' => true]);
        $block2 = $this->mockRandomization(['id' => 2, 'type' => 'page']);
        $block3 = $this->mockRandomization(['id' => 3, 'type' => 'page']);
        $block4 = $this->mockRandomization(['id' => 4, 'type' => 'block', 'isRandomized' => true]);

        $blockItem1 = $this->mockBlockItem(['id' => 101, 'pageId' => 1001, 'isRandomized' => true, 'weight' => 1]);
        $blockItem4 = $this->mockBlockItem(['id' => 104, 'pageId' => 1004, 'isRandomized' => true, 'weight' => 1]);
        $blockItem7 = $this->mockBlockItem(['id' => 107, 'pageId' => 1007, 'isRandomized' => true, 'weight' => 1]);

        $blockItem2 = $this->mockBlockItem(['id' => 102, 'pageId' => 1002]);
        $blockItem3 = $this->mockBlockItem(['id' => 103, 'pageId' => 1003]);

        $blockItem5 = $this->mockBlockItem(['id' => 105, 'pageId' => 1005]);
        $blockItem6 = $this->mockBlockItem(['id' => 106, 'pageId' => 1006]);

        $blockItem8 = $this->mockBlockItem(['id' => 108, 'blockId' => 2, 'isRandomized' => true, 'weight' => 1]);
        $blockItem9 = $this->mockBlockItem(['id' => 109, 'blockId' => 3, 'isRandomized' => true, 'weight' => 1]);

        $block1->setItems(new ArrayCollection([$blockItem1, $blockItem4, $blockItem7]));
        $block2->setItems(new ArrayCollection([$blockItem2, $blockItem3]));
        $block3->setItems(new ArrayCollection([$blockItem5, $blockItem6]));
        $block4->setItems(new ArrayCollection([$blockItem8, $blockItem9]));

        $survey->setRandomization(new ArrayCollection([$block1, $block2, $block3, $block4]));

        return $survey;
    }

    /**
     * P5, P6 are randomized - 2 combinations, P1, P2 are randomized - 2 combinations
     * 3 blocks with 2 pages inside are randomized - 6 combinations
     * total of 18 unique combinations
     */
    public function sample6(): Document\Survey
    {
        $survey = $this->mockSurvey();

        $page1 = $this->mockPage(['id' => 1, 'pageId' => 1001, 'code' => 'P1']);
        $page2 = $this->mockPage(['id' => 2, 'pageId' => 1002, 'code' => 'P2']);
        $page3 = $this->mockPage(['id' => 3, 'pageId' => 1003, 'code' => 'P3']);
        $page4 = $this->mockPage(['id' => 4, 'pageId' => 1004, 'code' => 'P4']);
        $page5 = $this->mockPage(['id' => 5, 'pageId' => 1005, 'code' => 'P5']);
        $page6 = $this->mockPage(['id' => 6, 'pageId' => 1006, 'code' => 'P6']);
        $page7 = $this->mockPage(['id' => 7, 'pageId' => 1007, 'code' => 'P7']);

        $survey->setPages(new ArrayCollection([$page1, $page2, $page3, $page4, $page5, $page6, $page7]));

        $block1 = $this->mockRandomization(['id' => 1, 'type' => 'page', 'isRandomized' => true]);
        $block2 = $this->mockRandomization(['id' => 2, 'type' => 'page']);
        $block3 = $this->mockRandomization(['id' => 3, 'type' => 'page', 'isRandomized' => true]);
        $block4 = $this->mockRandomization(['id' => 4, 'type' => 'block', 'isRandomized' => true]);

        $blockItem1 = $this->mockBlockItem(['id' => 101, 'pageId' => 1001, 'isRandomized' => true, 'weight' => 1]);
        $blockItem2 = $this->mockBlockItem(['id' => 102, 'pageId' => 1002, 'isRandomized' => true, 'weight' => 1]);

        $blockItem3 = $this->mockBlockItem(['id' => 103, 'pageId' => 1003]);
        $blockItem4 = $this->mockBlockItem(['id' => 104, 'pageId' => 1004]);

        $blockItem5 = $this->mockBlockItem(['id' => 105, 'pageId' => 1006, 'isRandomized' => true, 'weight' => 1]);
        $blockItem6 = $this->mockBlockItem(['id' => 106, 'pageId' => 1007, 'isRandomized' => true, 'weight' => 2]);

        $blockItem7 = $this->mockBlockItem(['id' => 107, 'blockId' => 1, 'isRandomized' => true, 'weight' => 1]);
        $blockItem8 = $this->mockBlockItem(['id' => 108, 'blockId' => 2, 'isRandomized' => true, 'weight' => 1]);
        $blockItem9 = $this->mockBlockItem(['id' => 109, 'blockId' => 3, 'isRandomized' => true, 'weight' => 5]);

        $block1->setItems(new ArrayCollection([$blockItem1, $blockItem2]));
        $block2->setItems(new ArrayCollection([$blockItem3, $blockItem4]));
        $block3->setItems(new ArrayCollection([$blockItem5, $blockItem6]));
        $block4->setItems(new ArrayCollection([$blockItem7, $blockItem8, $blockItem9]));

        $survey->setRandomization(new ArrayCollection([$block1, $block2, $block3, $block4]));

        return $survey;
    }

    public function weightSampleForWeights1(): Document\Survey
    {
        $survey = $this->mockSurvey();

        $block1 = $this->mockRandomization(['id' => 1, 'type' => 'page']);
        $block2 = $this->mockRandomization(['id' => 2, 'type' => 'page']);
        $block3 = $this->mockRandomization(['id' => 3, 'type' => 'page', 'isRandomized' => true]);
        $block4 = $this->mockRandomization(['id' => 4, 'type' => 'block', 'isRandomized' => true]);

        $blockItem1 = $this->mockBlockItem(['id' => 101, 'pageId' => 1001]);
        $blockItem2 = $this->mockBlockItem(['id' => 102, 'pageId' => 1002]);

        $blockItem3 = $this->mockBlockItem(['id' => 103, 'pageId' => 1003]);
        $blockItem4 = $this->mockBlockItem(['id' => 104, 'pageId' => 1004]);

        $blockItem5 = $this->mockBlockItem(['id' => 105, 'pageId' => 1005, 'isRandomized' => true, 'weight' => 1]);
        $blockItem6 = $this->mockBlockItem(['id' => 106, 'pageId' => 1006, 'isRandomized' => true, 'weight' => 2]);

        $blockItem7 = $this->mockBlockItem(['id' => 107, 'blockId' => 1, 'isRandomized' => true, 'weight' => 1]);
        $blockItem8 = $this->mockBlockItem(['id' => 108, 'blockId' => 2, 'isRandomized' => true, 'weight' => 1]);
        $blockItem9 = $this->mockBlockItem(['id' => 109, 'blockId' => 3, 'isRandomized' => true, 'weight' => 5]);

        $block1->setItems(new ArrayCollection([$blockItem1, $blockItem2]));
        $block2->setItems(new ArrayCollection([$blockItem3, $blockItem4]));
        $block3->setItems(new ArrayCollection([$blockItem5, $blockItem6]));
        $block4->setItems(new ArrayCollection([$blockItem7, $blockItem8, $blockItem9]));

        $survey->setRandomization(new ArrayCollection([$block1, $block2, $block3, $block4]));

        return $survey;
    }
}
