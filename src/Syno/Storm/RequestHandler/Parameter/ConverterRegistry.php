<?php

namespace Syno\Storm\RequestHandler\Parameter;

class ConverterRegistry
{
    private array $converters = [];

    public function __construct(iterable $converters)
    {
        /** @var ConverterInterface $convertService */
        foreach ($converters as $convertService) {
            $this->converters[$convertService->getId()] = $convertService;
        }
    }

    public function get(int $id): ?ConverterInterface
    {
        return $this->converters[$id] ?? null;
    }
}
