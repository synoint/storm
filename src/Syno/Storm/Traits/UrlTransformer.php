<?php
namespace Syno\Storm\Traits;

use Syno\Storm\Document;

trait UrlTransformer {

    /**
     * @param string $url
     * @param Document\Response $response
     *
     * @return string
     */
    protected function populateParameters(string $url, Document\Response $response)
    {
        foreach ($response->getParameters() as $parameter) {
            /**@var Document\Parameter $parameter */
            $url = str_replace('{' . $parameter->getCode() . '}', $parameter->getValue(), $url);
        }

        return $url;
    }
}
