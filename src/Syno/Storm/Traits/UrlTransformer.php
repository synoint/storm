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
    protected function populateHiddenValues(string $url, Document\Response $response)
    {
        foreach ($response->getHiddenValues() as $hiddenValue) {
            /**@var Document\HiddenValue $hiddenValue */
            $url = str_replace('{' . $hiddenValue->getCode() . '}', $hiddenValue->getValue(), $url);
        }

        return $url;
    }
}
