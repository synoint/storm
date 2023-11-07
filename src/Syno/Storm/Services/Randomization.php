<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class Randomization
{
    private Combination         $combinationService;
    private RandomizationWeight $randomizationWeightService;

    public function __construct(Combination $combinationService, RandomizationWeight $randomizationWeightService)
    {
        $this->combinationService         = $combinationService;
        $this->randomizationWeightService = $randomizationWeightService;
    }

    public function getRandomizedPaths(Document\Survey $survey): array
    {
        $weights['blocks'] = $this->randomizationWeightService->getWeights($survey, 'block');
        $weights['pages']  = $this->randomizationWeightService->getWeights($survey, 'page');

        $permutatedItems['blocks'] = $this->getBlocks($survey, $weights);
        $permutatedItems['pages']  = $this->getPages($survey, $weights);

        return $this->createSurveyPathCombinations($survey, $permutatedItems);
    }

    private function getBlocks(Document\Survey $survey, array $weights): array
    {
        $blockPagesCombinations = [];
        $pages                  = $survey->getPlainPages();
        $blocks                 = $survey->getRandomization()->toArray();
        $positionMap            = [];
        $randomizedBlockGroups  = $this->findRandomizedBlocks($survey);

        if (!count($randomizedBlockGroups)) {
            return [];
        }

        $combinationGroups = [];
        foreach ($randomizedBlockGroups as $blockId => $randomizedBlockGroup) {
            $combinationGroups[$blockId] = $this->combinationService->findCombinations($randomizedBlockGroup);
        }

        $combinationGroups = $this->mergeBlockCombinations($combinationGroups);

        foreach ($combinationGroups as $group => $combinations) {
            foreach ($combinations as $index => $combination) {
                foreach ($combination as $combinationBlockId) {
                    $items = $this->getBlockContents($blocks, $combinationBlockId);

                    foreach ($items['page'] as $randomizedItems) {
                        foreach ($randomizedItems as $randomizedItem) {
                            $blockPagesCombinations[$group]['combinations'][$index][] = $randomizedItem->getPage();

                            $positionMap[array_search($randomizedItem->getPage(),
                                $pages)] = array_search($randomizedItem->getPage(), $pages);
                        }

                        $weight                = $weights['blocks'][$combinationBlockId];
                        $blockCombinationCount = $this->randomizationWeightService->countBlockCombinations($combinations,
                            $combinationBlockId);

                        $blockPagesCombinations[$group]['item_weights'][$index] = round($weight / $blockCombinationCount,
                            2,
                            PHP_ROUND_HALF_DOWN);
                    }
                }

                asort($positionMap);

                $blockPagesCombinations[$group]['position_map'] = $positionMap;
            }
        }

        return $blockPagesCombinations;
    }

    private function getPages(Document\Survey $survey, array $weights): array
    {
        $pages = $survey->getPlainPages();

        $permutatedItemGroups = [];
        $pagesCombinations    = [];

        foreach ($this->findRandomizedPages($survey) as $randomizedPages) {
            $permutatedItemGroups[] = $this->combinationService->findCombinations($randomizedPages);
        }

        foreach ($permutatedItemGroups as $group => $permutatedItems) {
            $positionMap = [];

            foreach ($permutatedItems as $items) {
                foreach ($items as $item) {
                    $positionMap[array_search($item, $pages)] = array_search($item, $pages);
                }

                // find page weights
                $firstPageId          = $items[0];
                $weight               = $weights['pages'][$firstPageId];
                $pageCombinationCount = $this->randomizationWeightService->countPageCombinations($permutatedItems,
                    $firstPageId);

                $pagesCombinations[$group]['item_weights'][] = round($weight / $pageCombinationCount, 2,
                    PHP_ROUND_HALF_DOWN);
            }

            asort($positionMap);

            // assign correct position
            $permutatedItemsWithOriginalPosition = [];
            foreach ($permutatedItems as $permutatedItemsIndex => $items) {
                $itemCount = 0;
                foreach ($items as $item) {
                    $key = $this->getPositionMapIndexOf($positionMap, $itemCount);

                    $permutatedItemsWithOriginalPosition[$permutatedItemsIndex][$key] = $item;
                    $itemCount++;
                }
            }

            $pagesCombinations[$group]['position_map'] = $positionMap;
            $pagesCombinations[$group]['combinations'] = $permutatedItemsWithOriginalPosition;
        }

        return $pagesCombinations;
    }

    private function getBlockContents(array $blocks, int $blockId): array
    {
        $result['page'] = [];

        /** @var Document\Randomization $block */
        foreach ($blocks as $block) {
            if ($block->getId() == $blockId) {
                if ('block' !== $block->getType()) {
                    $result['page'][] = $block->getItems()->toArray();
                }
                else {
                    /** @var Document\BlockItem $blockItem */
                    foreach ($block->getItems()->toArray() as $blockItem) {
                        foreach ($this->getBlockChildren($blocks, $blockItem->getBlock()) as $res)  {
                            $result['page'][] = $res;
                        }

                    }
                }
            }
        }

        return $result;
    }

    private function getBlockChildren(array $blocks, int $blockId): array
    {
        $result = [];

        /** @var Document\Randomization $block */
        foreach ($blocks as $block) {
            if ($block->getId() == $blockId) {
                if ('block' !== $block->getType()) {
                    $result[] = $block->getItems()->toArray();
                    return $result;
                }

                /** @var Document\BlockItem $blockItem */
                foreach ($block->getItems() as $blockItem) {
                    /** @var Document\Randomization $block */
                    foreach ($blocks as $block) {
                        if ($block->getId() === $blockItem->getBlock()) {
                            $result[] = $block->getItems()->toArray();
                        }
                    }
                }
            }
        }

        return $result;
    }

    private function findRandomizedPages(Document\Survey $survey): array
    {
        $items = [];
        $i     = 0;

        /** @var Document\Randomization $randomizationBlock */
        foreach ($survey->getRandomization() as $randomizationBlock) {
            if ('page' === $randomizationBlock->getType() && $randomizationBlock->isRandomized()) {
                /** @var Document\BlockItem $randomizedItem */
                foreach ($randomizationBlock->getItems() as $randomizedItem) {
                    if ($randomizedItem->getRandomize()) {
                        $items[$i][] = $randomizedItem->getPage();
                    }
                }

                $i++;
            }
        }

        return $items;
    }

    private function findRandomizedBlocks(Document\Survey $survey): array
    {
        $items = [];

        /** @var Document\Randomization $randomizationBlock */
        foreach ($survey->getRandomization() as $randomizationBlock) {
            if ('block' === $randomizationBlock->getType() && $randomizationBlock->isRandomized()) {
                /** @var Document\BlockItem $randomizedItem */
                foreach ($randomizationBlock->getItems() as $randomizedItem) {
                    if ($randomizedItem->getRandomize()) {
                        $items[$randomizationBlock->getId()][] = $randomizedItem->getBlock();
                    }
                }
            }
        }

        return $items;
    }

    private function createSurveyPathCombinations(Document\Survey $survey, array $permutatedItems): array
    {
        $randomizedPaths = [];
        $pages           = $survey->getPlainPages();

        if (count($permutatedItems['blocks']) && count($permutatedItems['pages'])) {
            $allCombinations = [];
            foreach ($permutatedItems['blocks'] as $permutatedPageItems) {
                $allCombinations[] = $permutatedPageItems['combinations'];
            }

            $randomizedPaths['blocks']['combinations'] = $this->mergePageCombinations($allCombinations);

            foreach ($randomizedPaths['blocks']['combinations'] as $path) {
                $randomizedPaths['blocks']['item_weights'][] = $this->randomizationWeightService->findWeightByBlockPageId($permutatedItems['blocks'],
                    $path[array_key_first($path)]);
            }

            $res = $this->mergeCombinations($randomizedPaths['blocks'], $permutatedItems['pages'],
                $randomizedPaths['blocks']['item_weights']);

            $randomizedPaths['paths']   = $res['paths'];
            $randomizedPaths['weights'] = $res['weights'];
        }

        if (count($permutatedItems['blocks']) && !count($permutatedItems['pages'])) {
            $allCombinations = [];
            foreach ($permutatedItems['blocks'] as $permutatedPageItems) {
                $allCombinations[] = $permutatedPageItems['combinations'];
            }

            array_multisort(array_map('count', $allCombinations), SORT_DESC, $allCombinations);
            $randomizedPaths['paths'] = $this->mergePageCombinations($allCombinations);

            foreach ($randomizedPaths['paths'] as $path) {
                $randomizedPaths['weights'][] = $this->randomizationWeightService->findWeightByBlockPageId($permutatedItems['blocks'],
                    $path[array_key_first($path)]);
            }
        }

        if (!count($permutatedItems['blocks']) && count($permutatedItems['pages'])) {
            $allCombinations = [];
            foreach ($permutatedItems['pages'] as $permutatedPageItems) {
                $allCombinations[] = $permutatedPageItems['combinations'];
            }

            $randomizedPaths['paths'] = $this->mergePageCombinations($allCombinations);

            foreach ($randomizedPaths['paths'] as $path) {
                $randomizedPaths['weights'][] = $this->randomizationWeightService->findWeightByPageId($permutatedItems['pages'],
                    $path[array_key_first($path)]);
            }
        }

        $paths = [];

        foreach ($randomizedPaths['paths'] as $path) {
            $newPath = [];

            foreach ($pages as $position => $val) {
                if (in_array($val, $path)) {
                    $index = array_search($val, $path);
                    $newPath[$index] = $path[$index];

                } else {
                    $newPath[$position] = $val;
                }
            }

            ksort($newPath);
            $paths[] = $newPath;
        }

        $combinations = ['paths' => $paths, 'weights' => $randomizedPaths['weights']];

        return $this->reValidateCombinations($pages, $combinations);
    }

    private function mergeCombinations(array $allCombinations, array $childCombinations, array $weights): array
    {
        $randomizedPaths = [];

        foreach ($childCombinations as $childCombinationsIndex => $childCombinationItems) {
            foreach ($childCombinationItems['combinations'] as $permutatedPageItemIndex => $pageItems) { // pirmi 2 elementai

                foreach ($allCombinations['combinations'] as $blockItemsIndex => $blockItems) {
                    $blockItemWeight      = $weights[$blockItemsIndex];
                    $randomizedPathsTemp2 = $blockItems;

                    foreach ($pageItems as $key => $item) {
                        if (in_array($item, $blockItems)) {
                            $randomizedPathsTemp2 = $this->replaceElements($randomizedPathsTemp2, $pageItems);
                        } else {
                            $randomizedPathsTemp2[$key] = $item;
                        }
                    }

                    $index                                   = implode(',', $randomizedPathsTemp2);
                    $randomizedPaths['combinations'][$index] = $randomizedPathsTemp2;
                    $randomizedPaths['weights'][$index]      = $blockItemWeight + $childCombinationItems['item_weights'][$permutatedPageItemIndex];
                }
            }

            unset($childCombinations[$childCombinationsIndex]);
            if (count($childCombinations)) {
                $allCombinations = $randomizedPaths;
                $weights         = $randomizedPaths['weights'];
                $this->mergeCombinations($allCombinations, $childCombinations, $weights);
            }
        }

        $result = [];

        foreach ($randomizedPaths['combinations'] as $key => $val) {
            $result['paths'][]   = $val;
            $result['weights'][] = $randomizedPaths['weights'][$key];
        }

        return $result;
    }

    /**
     * Blocks might contain of other blocks that has blocks
     * Then these block items must replace parents blocks and be removed from combinations
     */
    private function mergeBlockCombinations(array $permutatedItems): array
    {
        $result = [];

        foreach ($permutatedItems as $keyA => $elemA) {
            $add = true;
            foreach ($elemA as $index => $elemArr) {
                foreach ($elemArr as $key => $val) {
                    if (isset($permutatedItems[$val])) {
                        unset($result[$val]);
                        $add     = false;
                        $tempArr = $permutatedItems[$keyA][$index];
                        unset($tempArr[$key]);

                        foreach ($permutatedItems[$val] as $bKey => $bElem) {
                            $newTemp = $tempArr;
                            array_splice($newTemp, $key, 0, $bElem);
                            $result[$keyA][] = $newTemp;
                        }
                    }
                }
            }

            if ($add) {
                $result[$keyA] = $elemA;
            }
        }

        return $result;
    }

    private function mergePageCombinations(array &$combinations, array &$result = [])
    {
        for ($i = 0; $i < count($combinations); $i++) {
            $c = 0;
            if (0 === count($result)) {
                $result = $combinations[0];
            } else {
                $tempResult = $result;
                for ($n = 0; $n < count($combinations[$i]); $n++) {
                    for ($m = 0; $m < count($tempResult); $m++) {
                        $mergedItems = [];
                        foreach ($combinations[$i][$n] as $index => $value) {
                            $mergedItems[$index] = $value;
                        }

                        foreach ($tempResult[$m] as $index => $value) {
                            if (in_array($value, $mergedItems)) {
                                $mergedItems = $this->replaceElements($mergedItems, $tempResult[$m]);
                            } else {
                                $mergedItems[$index] = $value;
                            }
                        }

                        $result[$c] = $mergedItems;
                        $c++;
                    }
                }
            }

            $combinations = array_slice($combinations, 1);

            $this->mergePageCombinations($combinations, $result);
        }

        return $result;
    }

    private function reValidateCombinations(array $pages, array $combinations): array
    {
        $uniqueCombinations = [];
        $pagesCount         = count($pages);

        $combinationsWithAllPages = [];
        foreach ($combinations['paths'] as $index => $combination) {
            if ($pagesCount === count(array_unique($combination))) {
                $combinationsWithAllPages['paths'][]   = $combination;
                $combinationsWithAllPages['weights'][] = $combinations['weights'][$index];
            }
        }

        $combinationStrings = [];
        foreach ($combinationsWithAllPages['paths'] as $index => $combination) {
            $combinationString = implode(', ', $combination);
            if (!in_array($combinationString, $combinationStrings)) {
                array_push($combinationStrings, $combinationString);
                $uniqueCombinations['paths'][]   = $combination;
                $uniqueCombinations['weights'][] = $combinationsWithAllPages['weights'][$index];
            }
        }

        return $uniqueCombinations;
    }

    private function replaceElements($randomizedPath, $newCombination): array
    {
        $result   = $randomizedPath;
        $continue = true;

        $i = 0;
        foreach ($randomizedPath as $key => $item) {
            if ($continue && in_array($item, $newCombination)) {
                foreach ($newCombination as $newItem) {
                    unset($result[array_search($newItem, $randomizedPath)]);
                }
                array_splice($result, $i, 0, $newCombination);
                $continue = false;
            }
            $i++;
        }

        $resultWithPositions = [];
        $i                   = 0;

        foreach ($randomizedPath as $key => $val) {
            $resultWithPositions[$key] = $result[$i];
            $i++;
        }

        return $resultWithPositions;
    }

    private function getPositionMapIndexOf($map, $position): int
    {
        $indexOf   = 0;
        $iteration = 0;

        foreach ($map as $key) {
            if ($iteration === $position) {
                $indexOf = $key;
            }

            $iteration++;
        }

        return $indexOf;
    }
}
