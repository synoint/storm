<?php

namespace Syno\Storm\Message;

use JsonSerializable;
use Syno\Storm\Document\Response;

class ProfilingSurvey implements JsonSerializable
{
    private string   $url;
    private Response $response;

    public function __construct(string $url, Response $response)
    {
        $this->url      = $url;
        $this->response = $response;
    }

    public function jsonSerialize(): array
    {
        return [
            'url'      => $this->url,
            'response' => $this->response,
        ];
    }
}
