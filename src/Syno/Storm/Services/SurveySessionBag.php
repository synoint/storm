<?php

namespace Syno\Storm\Services;

class SurveySessionBag
{
    public $surveyId;
    /** @var int */
    public $started;
    /** @var string */
    public $mode;

    public function __construct()
    {
        $this->started = time();
    }


}
