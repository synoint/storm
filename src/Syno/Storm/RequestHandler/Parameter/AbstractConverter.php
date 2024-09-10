<?php

namespace Syno\Storm\RequestHandler\Parameter;

use Doctrine\Common\Collections\Collection;
use Syno\Storm\Document\Parameter;

abstract class AbstractConverter implements ConverterInterface
{
    private const   UNRECOGNIZED_PREFIX = 'u_';
    private const   UNRECOGNIZED_NAME   = 'Unrecognized ';
    protected const PARAM_MALE_VALUE    = 'Male';
    protected const PARAM_FEMALE_VALUE  = 'Female';

    public function convert(Collection $parameters, array $availableConverters): Collection
    {
        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {

            $parameterConverter = $this->getAvailableConverter($availableConverters, $parameter->getCode());

            if ($parameterConverter && method_exists($this, $parameterConverter)) {

                $convertedValue = $this->$parameterConverter($parameter->getValue());

                if ($convertedValue === null) {
                    $parameters->add($this->createUnrecognizedParameter($parameter));
                }
            } else {
                $convertedValue = $parameter->getValue();
            }

            $parameter->setValue($convertedValue);
        }

        return $parameters;
    }

    public function getAvailableConverter(array $availableConverters, string $code): ?string
    {
        return $availableConverters[$code] ?? null;
    }

    protected function createUnrecognizedParameter(Parameter $parameter): Parameter
    {
        $unrecognizedParameter = new Parameter();

        $unrecognizedParameter->setName(self::UNRECOGNIZED_NAME . $parameter->getName());
        $unrecognizedParameter->setCode(self::UNRECOGNIZED_PREFIX . $parameter->getCode());
        $unrecognizedParameter->setUrlParam(self::UNRECOGNIZED_PREFIX . $parameter->getUrlParam());
        $unrecognizedParameter->setValue($parameter->getValue());

        return $unrecognizedParameter;
    }
}
