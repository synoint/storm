<?php
declare(strict_types=1);

namespace App\Tests\Unit\Syno\Storm\Services;

use App\Tests\Unit\RandomizationSamples;
use PHPUnit\Framework\TestCase;
use Syno\Storm\Document;
use Syno\Storm\Services;

final class RandomizationWeightTest extends TestCase
{
    use RandomizationSamples;

    private Document\Survey              $survey;
    private Services\Combination         $permutationService;
    private Services\RandomizationWeight $randomizationWeightService;

    public function setUp(): void
    {
        $this->permutationService         = new Services\Combination();
        $this->randomizationWeightService = new Services\RandomizationWeight();
    }

    public function testBlockWeights(): void
    {
        $weights = $this->randomizationWeightService->getWeights($this->weightSampleForWeights1(), 'block');

        // key is block id
        $this->assertEquals(1, $weights['1'], 'Error. Incorrectly returned weight');
        $this->assertEquals(1, $weights['2'], 'Error. Incorrectly returned weight');
        $this->assertEquals(5, $weights['3'], 'Error. Incorrectly returned weight');
    }

    public function testPageWeights(): void
    {
        $weights = $this->randomizationWeightService->getWeights($this->weightSampleForWeights1(), 'page');

        // key is page id
        $this->assertEquals(1, $weights['1005'], 'Error. Incorrectly returned weight');
        $this->assertEquals(2, $weights['1006'], 'Error. Incorrectly returned weight');
    }
}
