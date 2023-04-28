<?php

namespace Syno\Storm\Message;

use JsonSerializable;

class SurveyNotification implements JsonSerializable
{
    private int    $surveyId;
    private string $notificationType;
    private string $response;

    public function __construct(int $surveyId, string $notificationType, string $response)
    {
        $this->surveyId         = $surveyId;
        $this->notificationType = $notificationType;
        $this->response         = $response;
    }

    public function jsonSerialize(): array
    {
        return [
            'surveyId'         => $this->surveyId,
            'notificationType' => $this->notificationType,
            'response'         => $this->response,
        ];
    }
}
