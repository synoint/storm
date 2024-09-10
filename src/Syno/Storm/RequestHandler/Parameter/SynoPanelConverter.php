<?php

namespace Syno\Storm\RequestHandler\Parameter;

use Doctrine\Common\Collections\Collection;
use Syno\Storm\Document\Parameter;

class SynoPanelConverter implements Converter
{
    private const MALE_CODE   = 'M';
    private const FEMALE_CODE = 'F';

    public function convert(Collection $parameters): Collection
    {
        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {

            if ($parameter->getCode() === Parameter::PARAM_GENDER) {
                $parameter->setValue($this->convertGender($parameter->getValue()));
            }
        }

        return $parameters;
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
