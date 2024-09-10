<?php

namespace Syno\Storm\RequestHandler\Parameter;

use Doctrine\Common\Collections\Collection;
use Syno\Storm\Document\Parameter;

class Unifier
{
    public function unify(Collection $parameters): Collection
    {
        $source = $this->getSource($parameters);

        if ($source) {
            $converter  = $this->createConverter($source);
            $parameters = $converter ? $converter->convert($parameters) : $parameters;
        }

        return $parameters;
    }

    public function createConverter(string $source): ?Converter
    {
        $converter = null;

        switch ($source) {
            case 1:
            case 2:
                $converter = new CintConverter();
                break;
            case 3:
                $converter = new SynoPanelConverter();
                break;
            case 11:
                $converter = new PureSpectrumConverter();
                break;
        }

        return $converter;
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