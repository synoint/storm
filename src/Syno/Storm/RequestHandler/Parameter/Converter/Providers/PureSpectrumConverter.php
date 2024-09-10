<?php

namespace Syno\Storm\RequestHandler\Parameter\Converter\Providers;

use Doctrine\Common\Collections\Collection;
use Syno\Storm\RequestHandler\Parameter\Converter\AbstractConverter;

class PureSpectrumConverter extends AbstractConverter
{
    public const  SOURCE_ID    = 11;
    private const MALE_VALUE   = 111;
    private const FEMALE_VALUE = 112;
    private const CONVERTERS   =
        [
            'G'   => 'convertGender',
            'YOB' => 'convertAge'
        ];

    public function getId(): ?int
    {
        return self::SOURCE_ID;
    }

    public function getConvertedParameters(Collection $parameters): Collection
    {
        return $this->convert($parameters, self::CONVERTERS);
    }

    protected function convertAge(string $age): ?string
    {
        return ((int)$age) ? date("Y") - $age : null;
    }

    protected function convertGender(string $gender): ?string
    {
        switch ((int)$gender) {
            case self::MALE_VALUE:
                $gender = self::PARAM_MALE_VALUE;
                break;
            case self::FEMALE_VALUE:
                $gender = self::PARAM_FEMALE_VALUE;
                break;
            default:
                $gender = null;
        }

        return $gender;
    }
}