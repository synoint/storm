<?php
declare(strict_types=1);

namespace App\Tests\Unit\Syno\Storm\Services;

use PHPUnit\Framework\TestCase;
use Syno\Storm\Services;

final class CombinationTest extends TestCase
{
    private Services\Combination $combinationService;

    private array $elements1 = ['P1', 'P2', 'P3', 'P4', 'P5', 'P6'];
    private array $elements2 = ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7'];

    public function setUp(): void
    {
        $this->combinationService = new Services\Combination();
    }

    public function testDoNotBreakOnEmpty(): void
    {
        $result = $this->combinationService->findCombinations([]);

        $this->assertCount(0, $result);
    }

    public function testCombinationCount(): void
    {
        $result = $this->combinationService->findCombinations($this->elements1);

        $this->assertCount(count($this->elements1), $result);
    }

    public function testCombinationsAreUnique(): void
    {
        $result = $this->combinationService->findCombinations($this->elements2);
        $flattenCombinations = [];
        foreach ($result as $combination) {
            $flattenCombinations[] = implode(',', $combination);
        }

        $this->assertSameSize($result, array_unique($flattenCombinations));
    }
}
