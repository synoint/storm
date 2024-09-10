<?php

namespace Syno\Storm\RequestHandler\Parameter;

use Doctrine\Common\Collections\Collection;

interface Converter
{
    const MALE_VALUE   = 'Male';
    const FEMALE_VALUE = 'Female';

    public function convert(Collection $parameters): Collection;
}
