<?php

namespace Syno\Storm\Message;

use JsonSerializable;

class ProfilingSurvey implements JsonSerializable
{
    private string $url;
    private array  $data;

    public function __construct(string $url, array $data)
    {
        $this->url  = $url;
        $this->data = $data;
    }

    public function jsonSerialize(): array
    {
        return [
            'url'  => $this->url,
            'data' => $this->data,
        ];
    }
}
