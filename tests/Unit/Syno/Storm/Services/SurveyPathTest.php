<?php
declare(strict_types=1);

namespace App\Tests\Unit\Syno\Storm\Services;

use App\Tests\Traits\DocumentMockTrait;
use App\Tests\Unit\RandomizationSamples;
use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\TestCase;
use Syno\Storm\Document;
use Syno\Storm\Services;

final class SurveyPathTest extends TestCase
{
    use RandomizationSamples;
    use DocumentMockTrait;

    private Services\Randomization $randomizationService;
    private Services\SurveyPath    $surveyPathService;

    public function setUp(): void
    {
        $permutationService         = new Services\Permutation();
        $randomizationWeightService = new Services\RandomizationWeight();

        $this->randomizationService = new Services\Randomization($permutationService, $randomizationWeightService);

        $dmMock = $this->createMock(DocumentManager::class);

        $this->surveyPathService = new Services\SurveyPath($dmMock);
    }

    // 720 combinations, all with weight of 1
    public function testGetRandomizedPathsFromSample1(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample1());

        $surveyPaths = $this->mockSurveyPaths($randomizedItems);

        $pickedRandomizedPaths = $this->generateRandomizedPaths(7000, $surveyPaths);

        $this->assertCount(720, array_unique($pickedRandomizedPaths));
    }

    public function testGetRandomizedPathsFromSample2(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample2());

        $surveyPaths = $this->mockSurveyPaths($randomizedItems);

        $pickedRandomizedPaths = $this->generateRandomizedPaths(80, $surveyPaths);

        $this->assertCount(12, array_unique($pickedRandomizedPaths));
    }

    public function testGetRandomizedPathsFromSample3(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample3());

        $surveyPaths = $this->mockSurveyPaths($randomizedItems);

        $pickedRandomizedPaths = $this->generateRandomizedPaths(50, $surveyPaths);

        $this->assertCount(6, array_unique($pickedRandomizedPaths));
    }

    public function testGetRandomizedPathsFromSample4(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample4());

        $surveyPaths = $this->mockSurveyPaths($randomizedItems);

        $pickedRandomizedPaths = $this->generateRandomizedPaths(1500, $surveyPaths);

        $this->assertCount(144, array_unique($pickedRandomizedPaths));
    }

    public function testGetRandomizedPathsFromSample5(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample5());

        $surveyPaths = $this->mockSurveyPaths($randomizedItems);

        $pickedRandomizedPaths = $this->generateRandomizedPaths(1000, $surveyPaths);

        $this->assertCount(12, array_unique($pickedRandomizedPaths));
    }

    public function testGetRandomizedPathsFromSample6(): void
    {
        $randomizedItems = $this->randomizationService->getRandomizedPaths($this->sample6());

        $surveyPaths = $this->mockSurveyPaths($randomizedItems);

        $pickedRandomizedPaths = $this->generateRandomizedPaths(150, $surveyPaths);

        $this->assertCount(18, array_unique($pickedRandomizedPaths));
    }

    private function generateRandomizedPaths(int $requestSize, array $surveyPaths): array{
        $pickedRandomizedPaths = [];

        for ($i = 0; $i < $requestSize; $i++) {
            /** @var Document\SurveyPath $surveyPath */
            $surveyPath              = $this->surveyPathService->getRandomWeightedElement($surveyPaths);
            $pickedRandomizedPaths[] = $surveyPath->getDebugPath();
        }

        return $pickedRandomizedPaths;
    }
}
