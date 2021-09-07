<?php
namespace Syno\Storm\Traits;

use Syno\Storm\Document;

trait UrlTransformer {

    protected function populateParameters(string $url, Document\Response $response): string
    {
        foreach ($response->getParameters() as $parameter) {
            $url = str_replace('{' . $parameter->getCode() . '}', $parameter->getValue(), $url);
        }

        return $url;
    }
}
