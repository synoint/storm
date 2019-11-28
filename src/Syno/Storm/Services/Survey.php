<?php

namespace Syno\Storm\Services;

use Syno\Storm\Repository;

class Survey
{
    /** @var Repository\Survey */
    private $repository;

    /**
     * @param Repository\Survey $repository
     */
    public function __construct(Repository\Survey $repository)
    {
        $this->repository = $repository;
    }


}
