<?php

namespace Syno\Storm\RequestHandler\Parameter;

use Doctrine\Common\Collections\Collection;

interface ConverterInterface
{
    public function getId(): ?int;

    public function getConvertedParameters(Collection $parameters): Collection;
}
