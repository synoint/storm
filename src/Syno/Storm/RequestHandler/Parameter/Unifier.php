<?php

namespace Syno\Storm\RequestHandler\Parameter;

use Doctrine\Common\Collections\Collection;
use Syno\Storm\Document\Parameter;

class Unifier
{
    private ConverterRegistry $converterRegistry;

    public function __construct(ConverterRegistry $converterRegistry)
    {
        $this->converterRegistry = $converterRegistry;
    }

    public function unify(Collection $parameters): Collection
    {
        $source = $this->getSource($parameters);

        if ($source) {
            $converter  = $this->converterRegistry->get($source);
            $parameters = $converter ? $converter->getConvertedParameters($parameters) : $parameters;
        }

        return $parameters;
    }

    private function getSource(Collection $parameters): ?string
    {
        foreach ($parameters as $parameter) {
            if ($parameter->getCode() == Parameter::PARAM_SOURCE) {
                return $parameter->getValue();
            }
        }

        return null;
    }
}