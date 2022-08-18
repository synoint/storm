<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class RandomizationWeight
{
    public function getBlockWeights(Document\Survey $survey): array
    {
        $weights = [];
        $i       = 0;

        /** @var Document\Randomization $randomizationBlock */
        foreach ($survey->getRandomization()->toArray() as $randomizationBlock) {
            $increment = false;
            if ('block' === $randomizationBlock->getType()) {
                /** @var Document\BlockItem $randomizedItem */
                foreach ($randomizationBlock->getItems() as $randomizedItem) {
                    $weights[$i][$randomizedItem->getBlock()] = $randomizedItem->getWeight();

                    $increment = true;
                }
            }

            if ($increment) {
                $i++;
            }
        }

        return $this->convertToPercents($weights);
    }

    public function findWeightByPageId(array $permutatedItems, int $pageId): float
    {
        foreach ($permutatedItems as $permutatedItem) {
            $weights = $permutatedItem['item_weights'];

            foreach ($permutatedItem['combinations'] as $position => $combinationItems) {
                if ($combinationItems[0] === $pageId) {
                    return $weights[$position];
                }
            }
        }

        return 1;
    }

    public function getPageWeights(Document\Survey $survey): array
    {
        $weights = [];
        $i       = 0;

        /** @var Document\Randomization $randomizationBlock */
        foreach ($survey->getRandomization()->toArray() as $randomizationBlock) {
            $increment = false;
            if ('page' === $randomizationBlock->getType()) {
                /** @var Document\BlockItem $randomizedItem */
                foreach ($randomizationBlock->getItems() as $randomizedItem) {
                    if ($randomizedItem->getRandomize()) {
                        $increment                               = true;
                        $weights[$i][$randomizedItem->getPage()] = $randomizedItem->getWeight();
                    }
                }

                if ($increment) {
                    $i++;
                }
            }
        }

        return $this->convertToPercents($weights);
    }

    public function countBlockCombinations(array $combinations, int $blockId): int
    {
        $count = 0;

        foreach ($combinations as $combination) {
            if ($blockId === $combination[0]) {
                $count++;
            }
        }

        return $count;
    }

    public function countPageCombinations(array $combinations, int $pageId): int
    {
        $count = 0;

        foreach ($combinations as $combination) {
            if ($pageId === $combination[0]) {
                $count++;
            }
        }

        return $count;
    }

    private function convertToPercents(array $weights): array
    {
        $result = [];

        foreach ($weights as $weightItems) {
            $totalCount = 0;
            foreach ($weightItems as $weight) {
                $totalCount += $weight;
            }

            $percentage = ($totalCount) ? round((100 / $totalCount), 2) : 0;

            foreach ($weightItems as $pageId => $weight) {
                $result[$pageId] = $percentage * $weight;
            }
        }

        return $result;
    }
}
