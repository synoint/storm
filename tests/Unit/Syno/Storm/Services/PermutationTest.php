<?php
declare(strict_types=1);

namespace App\Tests\Unit\Syno\Storm\Services;

use PHPUnit\Framework\TestCase;
use Syno\Storm\Services;

final class PermutationTest extends TestCase
{
    private Services\Permutation $permutationService;

    private array $elements1 = ['P1', 'P2', 'P3', 'P4','P5', 'P6'];

    // this has 5040 combinations
    private array $elements2 = ['P1', 'P2', 'P3', 'P4','P5', 'P6', 'P7'];

    public function setUp(): void
    {
        $this->permutationService = new Services\Permutation();
    }

    public function testCombinationCount(): void
    {
        $createdCombinations = $this->permutationService->permute($this->elements1)->getResult();

        $elementCount = count($this->elements1);

        $this->assertCount($this->calculatePossibleCombinationsCount($elementCount), $createdCombinations, 'Error. Missing combinations');
    }

    public function testCombinationsAreUnique(): void
    {
        $createdCombinations = $this->permutationService->permute($this->elements1)->getResult();

        $flattenCombinations = [];

        foreach ($createdCombinations as $combination) {
            $flattenCombinations[] = implode(',', $combination);
        }

        $this->assertSameSize($createdCombinations, array_unique($flattenCombinations), 'Error. Combinations got duplicates');
    }

    public function testCombinationLimit(): void{
        $createdCombinations = $this->permutationService->permute($this->elements2)->getResult();

        $this->assertCount(1000, $createdCombinations, 'Error. Limit is bigger than 1000');
    }

    private function calculatePossibleCombinationsCount(int $elementCount): int
    {
        $count = 1;
        for ($i = $elementCount; $i > 0; $i--) {
            $count *= $i;
        }

        return $count;
    }
}