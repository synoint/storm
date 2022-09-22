<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class Randomization
{
    private Permutation         $permutationService;
    private RandomizationWeight $randomizationWeightService;

    public function __construct(Permutation $permutationService, RandomizationWeight $randomizationWeightService)
    {
        $this->permutationService         = $permutationService;
        $this->randomizationWeightService = $randomizationWeightService;
    }

    public function getRandomizedPaths(Document\Survey $survey): array
    {
        $weights['blocks'] = $this->randomizationWeightService->getWeights($survey, 'block');
        $weights['pages']  = $this->randomizationWeightService->getWeights($survey, 'page');

        $permutatedItems['blocks'] = $this->getPermutatedBlockPages($survey, $weights);
        $permutatedItems['pages']  = $this->getPermutatedPages($survey, $weights);

        return $this->createSurveyPathCombinations($survey, $permutatedItems);
    }

    private function getPermutatedBlockPages(Document\Survey $survey, array $weights): array
    {
        $pages                                  = $survey->getPlainPages();
        $positionMap                            = [];
        $blockPagesCombinations['position_map'] = [];
        $blockPagesCombinations['combinations'] = [];
        $blockPagesCombinations['item_weights'] = [];

        $randomizedBlocks = $this->findRandomizedBlocks($survey);

        if (!count($randomizedBlocks)) {
            return [];
        }

        $permutatedItems = $this->permutationService->permute($randomizedBlocks)->getResult();

        foreach ($permutatedItems as $index => $items) {
            foreach ($items as $item) {
                foreach ($survey->getRandomization() as $randomizationBlock) {
                    if ($item === $randomizationBlock->getId() && 'page' === $randomizationBlock->getType()) {
                        /** @var Document\BlockItem $randomizedItem */
                        foreach ($randomizationBlock->getItems() as $randomizedItem) {
                            $blockPagesCombinations['combinations'][$index][] = $randomizedItem->getPage();

                            $positionMap[array_search($randomizedItem->getPage(),
                                $pages)] = array_search($randomizedItem->getPage(), $pages);
                        }
                    }
                }
            }

            asort($positionMap);

            $blockPagesCombinations['position_map'] = $positionMap;

            // find block weights
            $firstBlockId          = $items[0];
            $weight                = $weights['blocks'][$firstBlockId];
            $blockCombinationCount = $this->randomizationWeightService->countBlockCombinations($permutatedItems,
                $firstBlockId);

            $blockPagesCombinations['item_weights'][$index] = round($weight / $blockCombinationCount, 2, PHP_ROUND_HALF_DOWN);
        }

        return $blockPagesCombinations;
    }

    private function getPermutatedPages(Document\Survey $survey, array $weights): array
    {
        $pages = $survey->getPlainPages();

        $permutatedItemGroups = [];
        $pagesCombinations    = [];

        foreach ($this->findRandomizedPages($survey) as $randomizedPages) {
            $permutatedItemGroups[] = $this->permutationService->permute($randomizedPages)->getResult();
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

                $pagesCombinations[$group]['item_weights'][] = round($weight / $pageCombinationCount, 2, PHP_ROUND_HALF_DOWN);

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
                    $items[] = $randomizedItem->getBlock();
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
            $c = -1;

            foreach ($permutatedItems['blocks']['combinations'] as $blockItemsIndex => $blockItems) {
                $blockItemCount      = 0;
                $randomizedPathsTemp = [];
                $blockItemWeight     = 0;

                foreach ($blockItems as $item) {
                    $key             = $this->getPositionMapIndexOf($permutatedItems['blocks']['position_map'],
                        $blockItemCount);
                    $blockItemWeight = $permutatedItems['blocks']['item_weights'][$blockItemsIndex];

                    $randomizedPathsTemp[$key] = $item;
                    $blockItemCount++;
                }

                foreach ($permutatedItems['pages'] as $permutatedPageItems) {
                    $randomizedPathsTemp2 = $randomizedPathsTemp;

                    foreach ($permutatedPageItems['combinations'] as $pageItemIndex => $pageItems) {
                        $pageItemCount = 0;
                        foreach ($pageItems as $item) {
                            if (in_array($item, $randomizedPathsTemp)) {
                                $randomizedPathsTemp2 = $this->replaceElement($randomizedPathsTemp2, $item, $pageItems);
                            } else {
                                $key = $this->getPositionMapIndexOf($permutatedPageItems['position_map'], $pageItemCount);

                                $randomizedPathsTemp2[$key] = $item;
                                $pageItemCount++;
                            }
                        }

                        $counter                              = ++$c;
                        $randomizedPaths['paths'][$counter]   = $randomizedPathsTemp2;
                        $randomizedPaths['weights'][$counter] = $blockItemWeight + $permutatedPageItems['item_weights'][$pageItemIndex];
                    }
                }
            }
        }

        if (count($permutatedItems['blocks']) && !count($permutatedItems['pages'])) {
            foreach ($permutatedItems['blocks']['combinations'] as $permutationIndex => $items) {
                $itemCount = 0;

                foreach ($items as $item) {
                    $key = $this->getPositionMapIndexOf($permutatedItems['blocks']['position_map'], $itemCount);

                    $randomizedPaths['paths'][$permutationIndex][$key] = $item;
                    $randomizedPaths['weights'][$permutationIndex]     = $permutatedItems['blocks']['item_weights'][$permutationIndex];
                    $itemCount++;
                }
            }
        }

        if (!count($permutatedItems['blocks']) && count($permutatedItems['pages'])) {
            $allCombinations = [];
            foreach ($permutatedItems['pages'] as $permutatedPageItems) {
                $allCombinations[] = $permutatedPageItems['combinations'];
            }

            $randomizedPaths['paths'] = $this->mergeCombinations($allCombinations);

            foreach ($randomizedPaths['paths'] as $path) {
                $randomizedPaths['weights'][] = $this->randomizationWeightService->findWeightByPageId($permutatedItems['pages'],
                    $path[array_key_first($path)]);
            }
        }

        $paths = [];
        foreach ($randomizedPaths['paths'] as $path) {
            foreach ($pages as $index => $page) {
                if (!in_array($page, $path)) {
                    $path[$index] = $page;
                }
            }

            ksort($path);

            $paths[] = $path;
        }

        return ['paths' => $paths, 'weights' => $randomizedPaths['weights']];
    }

    private function replaceElement($randomizedPath, $newItem, $newCombination): array
    {
        $result = $randomizedPath;

        $continue = true;
        foreach ($randomizedPath as $key => $item) {
            if ($continue && in_array($item, $newCombination) && $item !== $newItem) {
                $result[$key] = $newItem;
                $continue = false;
            }
        }

        return $result;
    }

    private function mergeCombinations(array &$combinations, array &$result = [])
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
                        foreach ($tempResult[$m] as $index => $value) {
                            $mergedItems[$index] = $value;
                        }

                        foreach ($combinations[$i][$n] as $index => $value) {
                            $mergedItems[$index] = $value;
                        }

                        $result[$c] = $mergedItems;
                        $c++;
                    }
                }
            }

            $combinations = array_slice($combinations, 1);

            $this->mergeCombinations($combinations, $result);
        }

        return $result;
    }

    private function getPositionMapIndexOf($map, $position): int
    {
        $indexOf = 0;

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
