<?php

namespace Syno\Storm\RequestHandler\Parameter;

use Doctrine\Common\Collections\Collection;
use Syno\Storm\Document\Parameter;

class PureSpectrumConverter implements Converter
{
    private const MALE_CODE   = 111;
    private const FEMALE_CODE = 112;

    public function convert(Collection $parameters): Collection
    {
        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {

            if ($parameter->getCode() === Parameter::PARAM_GENDER) {
                $parameter->setValue($this->convertGender($parameter->getValue()));
            }

            if ($parameter->getCode() === Parameter::PARAM_YOB) {
                $parameter->setValue($this->convertAge($parameter->getValue()));
            }
        }

        return $parameters;
    }

    private function convertAge(string $age): string
    {
        return $age < 100 ? date("Y") - $age : $age;
    }

    private function convertGender(string $genderCode): string
    {
        switch ($genderCode) {
            case self::MALE_CODE:
                $genderCode = self::MALE_VALUE;
                break;
            case self::FEMALE_CODE:
                $genderCode = self::FEMALE_VALUE;
                break;
        }

        return $genderCode;
    }
}