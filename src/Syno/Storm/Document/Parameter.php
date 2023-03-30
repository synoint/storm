<?php

namespace Syno\Storm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JsonSerializable;

/**
 * @ODM\EmbeddedDocument
 */
class Parameter implements JsonSerializable
{
    public const PARAM_SOURCE      = 'SOURCE';
    public const PARAM_RESPONSE_ID = 'RID';
    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $code;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $urlParam;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $value;

    public function jsonSerialize()
    {
        return [
            'name'     => $this->name,
            'code'     => $this->code,
            'urlParam' => $this->urlParam,
            'value'    => $this->value
        ];
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return self
     */
    public function setCode(string $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUrlParam()
    {
        return $this->urlParam;
    }

    /**
     * @param string $urlParam
     *
     * @return self
     */
    public function setUrlParam(string $urlParam)
    {
        $this->urlParam = $urlParam;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setValue(string $value)
    {
        $this->value = $value;

        return $this;
    }
}
