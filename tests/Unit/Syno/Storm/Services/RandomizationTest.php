<?php
declare(strict_types=1);

namespace App\Tests\Unit\Syno\Storm\Services;

use PHPUnit\Framework\TestCase;
use Syno\Storm\Services;

final class RandomizationTest extends TestCase
{
    private Services\Permutation $permutationService;
    private Services\Randomization $randomizationService;

    private array $elements1 = ['P1', 'P2', 'P3', 'P4','P5', 'P6'];

    private array $elements2 = ['P1', 'P2', 'P3', 'P4','P5', 'P6', 'P7'];

    public function setUp(): void
    {
//        $permutationService = $this->getMockBuilder('Syno\Services\Permutation\Permutation')->getMock();
//        $randomizationWeightService = $this->getMockBuilder('Syno\Services\RandomizationWeight')->getMock();
//
//        $randomizationService = new Services\Randomization($permutationService, $randomizationWeightService);
        $randomizationService = $this->getMockBuilder('Syno\Services\Randomization')->enableOriginalConstructor();
            dd($randomizationService);

//        dd($permutation);
//
//        $randomization = $this->getMockBuilder('Syno\Services\Randomization\Randomization')->

//        $this->permutationService = new Services\Permutation();
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

        $this->assertEquals(count($createdCombinations), 1000, 'Error. Limit is bigger than 1000');
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
