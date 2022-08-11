<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class Randomization
{
    private Permutation $permutationService;

    public function __construct(Permutation $permutationService)
    {
        $this->permutationService = $permutationService;
    }

    public function getRandomizedPaths(Document\Survey $survey): array
    {
        $permutatedPages      = $this->getPermutatedPages($survey);
        $permutatedBlockPages = $this->getPermutatedBlockPages($survey);

        $permutatedItems['blocks'] = $permutatedBlockPages;
        $permutatedItems['pages']  = $permutatedPages;

        return $this->createSurveyPathCombinations($survey, $permutatedItems);
    }

    private function getPermutatedBlockPages(Document\Survey $survey): array
    {
        $pages                                  = $survey->getPlainPages();
        $positionMap                            = [];
        $blockPagesCombinations['position_map'] = [];
        $blockPagesCombinations['combinations'] = [];

        $permutatedItems = $this->permutationService->permute($this->findRandomizedBlocks($survey))->getResult();

        foreach ($permutatedItems as $index => $items) {
            foreach ($items as $item) {
                foreach ($survey->getRandomizationBlocks()->toArray() as $randomizationBlock) {
                    if ($item === $randomizationBlock->getId() && 'page' === $randomizationBlock->getType()) {
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
        }

        return $blockPagesCombinations;
    }

    private function getPermutatedPages(Document\Survey $survey): array
    {
        $pages                = $survey->getPlainPages();
        $positionMap          = [];
        $permutatedItemGroups = [];
        $pagesCombinations    = [];

        foreach ($this->findRandomizedPages($survey) as $randomizedPages) {
            $permutatedItemGroups[] = $this->permutationService->permute($randomizedPages)->getResult();
        }

        foreach ($permutatedItemGroups as $group => $permutatedItems) {
            foreach ($permutatedItems as $items) {
                foreach ($items as $item) {
                    $positionMap[array_search($item, $pages)] = array_search($item, $pages);
                }
            }
            asort($positionMap);

            $pagesCombinations[$group]['position_map'] = $positionMap;
            $pagesCombinations[$group]['combinations'] = $permutatedItems;
        }

        return $pagesCombinations;
    }

    private function findRandomizedPages(Document\Survey $survey): array
    {
        $items = [];
        $i     = 0;

        /** @var Document\RandomizationBlock $randomizationBlock */
        foreach ($survey->getRandomizationBlocks()->toArray() as $randomizationBlock) {
            $increment = false;
            /** *************************************************************** */
            /** TODO manager will send a flag if block has randomization or not */
            /** *************************************************************** */
            if ('page' === $randomizationBlock->getType()) {
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

        /** @var Document\RandomizationBlock $randomizationBlock */
        foreach ($survey->getRandomizationBlocks()->toArray() as $randomizationBlock) {
            /** *************************************************************** */
            /** TODO manager will send a flag if block has randomization or not */
            /** *************************************************************** */
            if ('block' === $randomizationBlock->getType()) {
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

            foreach ($permutatedItems['blocks']['combinations'] as $blockItems) {
                $blockItemCount      = 0;
                $randomizedPathsTemp = [];

                foreach ($blockItems as $item) {
                    $key = $this->getPositionMapIndexOf($permutatedItems['blocks']['position_map'], $blockItemCount);

                    $randomizedPathsTemp[$key] = $item;
                    $blockItemCount++;
                }

                foreach ($permutatedItems['pages'] as $permutatedPageItems) {
                    $randomizedPathsTemp2 = $randomizedPathsTemp;

                    foreach ($permutatedPageItems['combinations'] as $pageItems) {
                        $pageItemCount = 0;
                        foreach ($pageItems as $item) {
                            $key = $this->getPositionMapIndexOf($permutatedPageItems['position_map'], $pageItemCount);

                            $randomizedPathsTemp2[$key] = $item;
                            $pageItemCount++;
                        }
                        $randomizedPaths[$c++] = $randomizedPathsTemp2;
                    }
                }
            }

        }

        if (count($permutatedItems['blocks']) && !count($permutatedItems['pages'])) {
            foreach ($permutatedItems['blocks']['combinations'] as $permutationIndex => $items) {
                $itemCount = 0;

                foreach ($items as $item) {
                    $key = $this->getPositionMapIndexOf($permutatedItems['blocks']['position_map'], $itemCount);

                    $randomizedPaths[$permutationIndex][$key] = $item;
                    $itemCount++;
                }
            }
        }

        if (!count($permutatedItems['blocks']) && count($permutatedItems['pages'])) {
            foreach ($permutatedItems['pages'] as $permutatedPageItems) {
                foreach ($permutatedPageItems['combinations'] as $permutationIndex => $items) {
                    $itemCount = 0;

                    foreach ($items as $item) {
                        $key = $this->getPositionMapIndexOf($permutatedPageItems['position_map'], $itemCount);

                        $randomizedPaths[$permutationIndex][$key] = $item;
                        $itemCount++;
                    }
                }
            }
        }

        $paths = [];
        foreach ($randomizedPaths as $path) {
            foreach ($pages as $index => $page) {
                if (!in_array($page, $path)) {
                    $path[$index] = $page;
                }
            }

            ksort($path);

            $paths[] = $path;
        }

        return $paths;
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

    public function getRandomWeightedElement(array $paths): ?Document\SurveyPath
    {
        $weightedValues = [];
        /** @var Document\SurveyPath $path */
        foreach ($paths as $index => $path) {
            $weightedValues[$index] = $path->getWeight();
        }

        $rand = mt_rand(1, (int) array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $paths[$key];
            }
        }

        return null;
    }
}
