<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: lily.proto

namespace Palm\Lily\V1\PandocTask;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>palm.lily.v1.PandocTask.Input</code>
 */
class Input extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.palm.lily.v1.PandocTask.Format format = 1;</code>
     */
    protected $format = 0;
    /**
     * Generated from protobuf field <code>bytes payload = 2;</code>
     */
    protected $payload = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $format
     *     @type string $payload
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Lily::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.palm.lily.v1.PandocTask.Format format = 1;</code>
     * @return int
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Generated from protobuf field <code>.palm.lily.v1.PandocTask.Format format = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setFormat($var)
    {
        GPBUtil::checkEnum($var, \Palm\Lily\V1\PandocTask\Format::class);
        $this->format = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>bytes payload = 2;</code>
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Generated from protobuf field <code>bytes payload = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setPayload($var)
    {
        GPBUtil::checkString($var, False);
        $this->payload = $var;

        return $this;
    }

}
