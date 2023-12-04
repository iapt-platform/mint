<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: tulip.proto

namespace Mint\Tulip\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>mint.tulip.v1.UpdateIndexRequest</code>
 */
class UpdateIndexRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>int32 book = 1;</code>
     */
    protected $book = 0;
    /**
     * Generated from protobuf field <code>optional int32 paragraph = 2;</code>
     */
    protected $paragraph = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $book
     *     @type int $paragraph
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Tulip::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>int32 book = 1;</code>
     * @return int
     */
    public function getBook()
    {
        return $this->book;
    }

    /**
     * Generated from protobuf field <code>int32 book = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setBook($var)
    {
        GPBUtil::checkInt32($var);
        $this->book = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>optional int32 paragraph = 2;</code>
     * @return int
     */
    public function getParagraph()
    {
        return isset($this->paragraph) ? $this->paragraph : 0;
    }

    public function hasParagraph()
    {
        return isset($this->paragraph);
    }

    public function clearParagraph()
    {
        unset($this->paragraph);
    }

    /**
     * Generated from protobuf field <code>optional int32 paragraph = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setParagraph($var)
    {
        GPBUtil::checkInt32($var);
        $this->paragraph = $var;

        return $this;
    }

}
