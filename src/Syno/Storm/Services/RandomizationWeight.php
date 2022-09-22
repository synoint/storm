<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

use Syno\Storm\Document;

class RandomizationWeight
{
    public function getWeights(Document\Survey $survey, string $type): array
    {
        $weights = [];
        $i       = 0;

        /** @var Document\Randomization $randomizationBlock */
        foreach ($survey->getRandomization() as $randomizationBlock) {
            // page type
            if ('page' === $type && 'page' === $randomizationBlock->getType() && $randomizationBlock->isRandomized()) {
                /** @var Document\BlockItem $randomizedItem */
                foreach ($randomizationBlock->getItems() as $randomizedItem) {
                    if ($randomizedItem->getRandomize()) {
                        $weights[$randomizedItem->getPage()] = $randomizedItem->getWeight();//[$i]
                    }
                }

                $i++;
            }

            // block type
            if ('block' === $type && 'block' === $randomizationBlock->getType() && $randomizationBlock->isRandomized()) {
                /** @var Document\BlockItem $randomizedItem */
                foreach ($randomizationBlock->getItems() as $randomizedItem) {
                    $weights[$randomizedItem->getBlock()] = $randomizedItem->getWeight();//[$i]
                }

                $i++;
            }
        }

        return $weights;
    }

    public function findWeightByPageId(array $permutatedItems, int $pageId): float
    {
        foreach ($permutatedItems as $permutatedItem) {
            $weights = $permutatedItem['item_weights'];

            foreach ($permutatedItem['combinations'] as $position => $combinationItems) {

                if ($combinationItems[array_key_first($combinationItems)] === $pageId) {
                    return $weights[$position];
                }
            }
        }

        return 1;
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

    /** Currently not in use */
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
