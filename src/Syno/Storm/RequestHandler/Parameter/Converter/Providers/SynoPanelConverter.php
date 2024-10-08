<?php

namespace Syno\Storm\RequestHandler\Parameter\Converter\Providers;

use Doctrine\Common\Collections\Collection;
use Syno\Storm\RequestHandler\Parameter\Converter\AbstractConverter;

class SynoPanelConverter extends AbstractConverter
{
    public const  SOURCE_ID    = 3;
    private const MALE_VALUE   = 'M';
    private const FEMALE_VALUE = 'F';
    private const CONVERTERS   =
        [
            'G' => 'convertGender'
        ];

    public function getId(): ?int
    {
        return self::SOURCE_ID;
    }

    public function getConvertedParameters(Collection $parameters): Collection
    {
        return $this->convert($parameters, self::CONVERTERS);
    }

    protected function convertGender(string $gender): ?string
    {
        if(in_array($gender, [self::PARAM_MALE_VALUE, self::PARAM_FEMALE_VALUE])){
            return $gender;
        }

        switch ($gender) {
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
