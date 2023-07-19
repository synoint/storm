<?php
declare(strict_types=1);

namespace App\Tests\Unit\Syno\Storm\Services;

use App\Tests\Unit\RandomizationSamples;
use PHPUnit\Framework\TestCase;
use Syno\Storm\Services;

final class RandomizationTest extends TestCase
{
    use RandomizationSamples;

    private Services\Randomization $randomizationService;

    public function setUp(): void
    {
        $permutationService         = new Services\Combination();
        $randomizationWeightService = new Services\RandomizationWeight();

        $this->randomizationService = new Services\Randomization($permutationService, $randomizationWeightService);
    }

    public function testGetRandomizedPathsWithSample1(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample1());

        $this->assertCount(720, $randomizedItems['paths'], 'Error. Incorrect amount of combinations for sample 1');
        $this->assertCount(720, $randomizedItems['weights'], 'Error. Incorrect amount of weights for sample 1');

        $flattenCombinations = $this->flattenCombinations($randomizedItems['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations),
            'Error. Combinations got duplicates for sample 1');
    }

    public function testGetRandomizedPathsWithSample2(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample2());

        $this->assertCount(12, $randomizedItems['paths'], 'Error. Incorrect amount of combinations for sample 2');
        $this->assertCount(12, $randomizedItems['weights'], 'Error. Incorrect amount of weights for sample 2');

        $flattenCombinations = $this->flattenCombinations($randomizedItems['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations),
            'Error. Combinations got duplicates for sample 2');
    }

    public function testGetRandomizedPathsWithSample3(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample3());

        $this->assertCount(6, $randomizedItems['paths'], 'Error. Incorrect amount of combinations for sample 3');
        $this->assertCount(6, $randomizedItems['weights'], 'Error. Incorrect amount of weights for sample 3');

        $flattenCombinations = $this->flattenCombinations($randomizedItems['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations),
            'Error. Combinations got duplicates for sample 3');
    }

    public function testGetRandomizedPathsWithSample4(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample4());

        $this->assertCount(144, $randomizedItems['paths'], 'Error. Incorrect amount of combinations for sample 4');
        $this->assertCount(144, $randomizedItems['weights'], 'Error. Incorrect amount of weights for sample 4');

        $flattenCombinations = $this->flattenCombinations($randomizedItems['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations),
            'Error. Combinations got duplicates for sample 4');
    }

    public function testGetRandomizedPathsWithSample5(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample5());

        $this->assertCount(12, $randomizedItems['paths'], 'Error. Incorrect amount of combinations for sample 5');
        $this->assertCount(12, $randomizedItems['weights'], 'Error. Incorrect amount of weights for sample 5');

        $flattenCombinations = $this->flattenCombinations($randomizedItems['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations),
            'Error. Combinations got duplicates for sample 5');
    }

    public function testGetRandomizedPathsWithSample6(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample6());

        $this->assertCount(24, $randomizedItems['paths'], 'Error. Incorrect amount of combinations for sample 6');
        $this->assertCount(24, $randomizedItems['weights'], 'Error. Incorrect amount of weights for sample 6');

        $flattenCombinations = $this->flattenCombinations($randomizedItems['paths']);

        $this->assertCount(24, array_unique($flattenCombinations),
            'Error. Combinations got duplicates for sample 6');
    }

    public function testGetRandomizedPathsWithSample7(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample7());

        $this->assertCount(4, $randomizedItems['paths'], 'Error. Incorrect amount of combinations for sample 7');
        $this->assertCount(4, $randomizedItems['weights'], 'Error. Incorrect amount of weights for sample 7');

        $flattenCombinations = $this->flattenCombinations($randomizedItems['paths']);

        $this->assertCount(4, array_unique($flattenCombinations),
            'Error. Combinations got duplicates for sample 7');
    }

    public function testGetRandomizedPathsWithSample8(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample8());

        $this->assertCount(24, $randomizedItems['paths'], 'Error. Incorrect amount of combinations for sample 8');
        $this->assertCount(24, $randomizedItems['weights'], 'Error. Incorrect amount of weights for sample 8');

        $flattenCombinations = $this->flattenCombinations($randomizedItems['paths']);

        $this->assertCount(24, array_unique($flattenCombinations),
            'Error. Combinations got duplicates for sample 8');
    }

    private function flattenCombinations(array $combinations): array
    {
        $flattenCombinations = [];

        foreach ($combinations as $combination) {
            $flattenCombinations[] = implode(',', $combination);
        }

        return $flattenCombinations;
    }
}
