<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

class Combination
{
    /**
     * Returns all possible combinations with "equal position rights" for each element
     */
    public function findCombinations($elements = []): array
    {
        $result         = [];
        $numberElements = count($elements);
        
        if (0 < $numberElements) {
            for ($i = 0; $i < $numberElements; $i++) {
                $result[] = $elements;
                array_unshift($elements, array_pop($elements));
            }
        }
        
        return $result;
    }
}
