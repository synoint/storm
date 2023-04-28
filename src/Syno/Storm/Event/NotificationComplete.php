<?php

namespace Syno\Storm\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Syno\Storm\Document\Response;
use Syno\Storm\Document\Survey;

class NotificationComplete extends Event
{
    private Survey   $survey;
    private Response $response;

    public function __construct(Survey $survey, Response $response)
    {
        $this->survey   = $survey;
        $this->response = $response;
    }

    public function getSurvey(): Survey
    {
        return $this->survey;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
