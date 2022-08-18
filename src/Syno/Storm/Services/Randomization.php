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
        $weights['blocks'] = $this->randomizationWeightService->getBlockWeights($survey);
        $weights['pages']  = $this->randomizationWeightService->getPageWeights($survey);

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
//dd($this->findRandomizedBlocks($survey));

        $randomizedBlocks = $this->findRandomizedBlocks($survey);
        if (!count($randomizedBlocks)) {
            return [];
        }

        $permutatedItems = $this->permutationService->permute($randomizedBlocks)->getResult();
//dump('----');
//dd($permutatedItems);
        foreach ($permutatedItems as $index => $items) {
            foreach ($items as $item) {
                foreach ($survey->getRandomization()->toArray() as $randomizationBlock) {
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
//dd($items);
            // find block weights
            $firstBlockId          = $items[0];
            $weight                = $weights['blocks'][$firstBlockId];
            $blockCombinationCount = $this->randomizationWeightService->countBlockCombinations($permutatedItems,
                $firstBlockId);

            $blockPagesCombinations['item_weights'][$index] = round($weight / $blockCombinationCount, 2);
        }

        return $blockPagesCombinations;
    }

    private function getPermutatedPages(Document\Survey $survey, array $weights): array
    {
        $pages = $survey->getPlainPages();
//        $positionMap = [];

        $permutatedItemGroups = [];
        $pagesCombinations    = [];

        foreach ($this->findRandomizedPages($survey) as $randomizedPages) {
            $permutatedItemGroups[] = $this->permutationService->permute($randomizedPages)->getResult();
        }

        foreach ($permutatedItemGroups as $group => $permutatedItems) {
            $positionMap = [];
            foreach ($permutatedItems as $items) {
                foreach ($items as $item) {
//                    if ($group > 0) {
//                        dump($item);
//                        dump(array_search($item, $pages));
//                    }

                    $positionMap[array_search($item, $pages)] = array_search($item, $pages);
                }

                // find page weights
                $firstPageId          = $items[0];
                $weight               = $weights['pages'][$firstPageId];
                $pageCombinationCount = $this->randomizationWeightService->countPageCombinations($permutatedItems,
                    $firstPageId);

                $pagesCombinations[$group]['item_weights'][] = round($weight / $pageCombinationCount, 2);

            }
            asort($positionMap);

            $pagesCombinations[$group]['position_map'] = $positionMap;
            $pagesCombinations[$group]['combinations'] = $permutatedItems;
        }
//dump('-----');
//dd($pagesCombinations[1]);
        return $pagesCombinations;
    }

    private function findRandomizedPages(Document\Survey $survey): array
    {
        $items = [];
        $i     = 0;

        /** @var Document\Randomization $randomizationBlock */
        foreach ($survey->getRandomization()->toArray() as $randomizationBlock) {
            $increment = false;

            if ('page' === $randomizationBlock->getType() && $randomizationBlock->isRandomized()) {
                /** @var Document\BlockItem $randomizedItem */
                foreach ($randomizationBlock->getItems() as $randomizedItem) {
                    if ($randomizedItem->getRandomize()) {
                        $items[$i][] = $randomizedItem->getPage();
                        $increment   = true;
                    }
                }

                if ($increment) {
                    $i++;
                }
            }
        }

        return $items;
    }

    private function findRandomizedBlocks(Document\Survey $survey): array
    {
        $items = [];

        /** @var Document\Randomization $randomizationBlock */
        foreach ($survey->getRandomization()->toArray() as $randomizationBlock) {
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
                            $key = $this->getPositionMapIndexOf($permutatedPageItems['position_map'], $pageItemCount);

                            $randomizedPathsTemp2[$key] = $item;
                            $pageItemCount++;
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
//dump($permutatedItems['pages'][1]);
        if (!count($permutatedItems['blocks']) && count($permutatedItems['pages'])) {
            foreach ($permutatedItems['pages'] as $permutatedPageItems) {
                foreach ($permutatedPageItems['combinations'] as $permutationIndex => $items) {
                    $itemCount = 0;

                    foreach ($items as $itemIndex => $item) {
                        $key = $this->getPositionMapIndexOf($permutatedPageItems['position_map'], $itemCount);

                        $randomizedPaths['paths'][$permutationIndex][$key] = $item;
                        $randomizedPaths['weights'][$permutationIndex]     = $permutatedPageItems['item_weights'][$itemIndex];
                        $itemCount++;
                    }
                }
            }
        }

//        dd($randomizedPaths);
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

    private function doSomething($combinations, $result = [])
    {
        for ($i = 0; $i < count($combinations); $i++) {
            if (empty($result)) {
                $result = $combinations[$i];
            }

            unset($combinations[$i]);

            $this->doSomething($combinations, $result);
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
