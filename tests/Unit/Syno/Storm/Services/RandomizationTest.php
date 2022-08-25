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

    private array $elements1 = ['P1', 'P2', 'P3', 'P4', 'P5', 'P6'];
    private array $elements2 = ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7'];

    public function setUp(): void
    {
        $permutationService         = new Services\Permutation();
        $randomizationWeightService = new Services\RandomizationWeight();

        $this->randomizationService = new Services\Randomization($permutationService, $randomizationWeightService);
    }

    public function testGetRandomizedPathsWithSample1(): void
    {
        $paths = $this->randomizationService->getRandomizedPaths($this->sample1());

        $this->assertCount(720, $paths['paths'], 'Error. Incorrect amount of combinations for sample 1');
        $this->assertCount(720, $paths['weights'], 'Error. Incorrect amount of weights for sample 1');

        $flattenCombinations = $this->flattenCombinations($paths['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations), 'Error. Combinations got duplicates for 1');
    }

    public function testGetRandomizedPathsWithSample2(): void
    {
        $paths = $this->randomizationService->getRandomizedPaths($this->sample2());

        $this->assertCount(12, $paths['paths'], 'Error. Incorrect amount of combinations for sample 2');
        $this->assertCount(12, $paths['weights'], 'Error. Incorrect amount of weights for sample 2');

        $flattenCombinations = $this->flattenCombinations($paths['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations), 'Error. Combinations got duplicates for 2');
    }

    public function testGetRandomizedPathsWithSample3(): void
    {
        $paths = $this->randomizationService->getRandomizedPaths($this->sample3());

        $this->assertCount(6, $paths['paths'], 'Error. Incorrect amount of combinations for sample 3');
        $this->assertCount(6, $paths['weights'], 'Error. Incorrect amount of weights for sample 3');

        $flattenCombinations = $this->flattenCombinations($paths['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations), 'Error. Combinations got duplicates for 3');
    }

    public function testGetRandomizedPathsWithSample4(): void
    {
        $paths = $this->randomizationService->getRandomizedPaths($this->sample4());

        $this->assertCount(144, $paths['paths'], 'Error. Incorrect amount of combinations for sample 4');
        $this->assertCount(144, $paths['weights'], 'Error. Incorrect amount of weights for sample 4');

        $flattenCombinations = $this->flattenCombinations($paths['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations), 'Error. Combinations got duplicates for 4');
    }

    public function testGetRandomizedPathsWithSample5(): void
    {
        $paths = $this->randomizationService->getRandomizedPaths($this->sample5());

        $this->assertCount(6, $paths['paths'], 'Error. Incorrect amount of combinations for sample 5');
        $this->assertCount(6, $paths['weights'], 'Error. Incorrect amount of weights for sample 5');

        $flattenCombinations = $this->flattenCombinations($paths['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations), 'Error. Combinations got duplicates for 5');
    }

    public function testGetRandomizedPathsWithSample6(): void
    {
        $paths = $this->randomizationService->getRandomizedPaths($this->sample6());

        $this->assertCount(24, $paths['paths'], 'Error. Incorrect amount of combinations for sample 6');
        $this->assertCount(24, $paths['weights'], 'Error. Incorrect amount of weights for sample 6');

        $flattenCombinations = $this->flattenCombinations($paths['paths']);
        $this->assertSameSize($flattenCombinations, array_unique($flattenCombinations), 'Error. Combinations got duplicates for 6');
        dd($paths['paths']);
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
