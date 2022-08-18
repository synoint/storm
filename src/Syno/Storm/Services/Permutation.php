<?php
declare(strict_types=1);

namespace Syno\Storm\Services;

class Permutation
{
    private const LIMIT = 1000;

    private array $result;

    public function getResult(): array
    {
        return $this->result;
    }

    public function permute($source = [], $permutated = []): Permutation
    {
        if (empty($permutated)) {
            $this->result = [];
        }

        if (self::LIMIT === count($this->result)) {
            return $this;
        }

        if (empty($source)) {
            $this->result[] = $permutated;
        } else {
            for ($i = 0; $i < count($source); $i++) {
                $newPermutated   = $permutated;
                $newPermutated[] = $source[$i];
                $newSource       = array_merge(array_slice($source, 0, $i), array_slice($source, $i + 1));
                $this->permute($newSource, $newPermutated);
            }
        }

        return $this;
    }
}
