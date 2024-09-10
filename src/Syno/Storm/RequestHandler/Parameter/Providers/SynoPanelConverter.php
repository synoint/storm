<?php

namespace Syno\Storm\RequestHandler\Parameter\Providers;

use Doctrine\Common\Collections\Collection;
use Syno\Storm\RequestHandler\Parameter\AbstractConverter;

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

    protected function convertGender(string $genderCode): ?string
    {
        switch ($genderCode) {
            case self::MALE_VALUE:
                $genderCode = self::PARAM_MALE_VALUE;
                break;
            case self::FEMALE_VALUE:
                $genderCode = self::PARAM_FEMALE_VALUE;
                break;
            default:
                $genderCode = null;
        }

        return $genderCode;
    }
}
