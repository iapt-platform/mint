<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: tulip.proto

namespace Mint\Tulip\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>mint.tulip.v1.UploadDictionaryRequest</code>
 */
class UploadDictionaryRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string data = 1;</code>
     */
    protected $data = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $data
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Tulip::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string data = 1;</code>
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Generated from protobuf field <code>string data = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setData($var)
    {
        GPBUtil::checkString($var, True);
        $this->data = $var;

        return $this;
    }

}

