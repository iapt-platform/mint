<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: lily.proto

namespace Palm\Lily\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>palm.lily.v1.TexToRequest</code>
 */
class TexToRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>map<string, bytes> files = 1;</code>
     */
    private $files;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array|\Google\Protobuf\Internal\MapField $files
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Lily::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>map<string, bytes> files = 1;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Generated from protobuf field <code>map<string, bytes> files = 1;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setFiles($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::BYTES);
        $this->files = $arr;

        return $this;
    }

}

