<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Mint\Morus\V1;

/**
 * ----------------------------------------------------------------------------
 */
class MarkdownClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Mint\Morus\V1\MarkdownToHtmlRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ToHtml(\Mint\Morus\V1\MarkdownToHtmlRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/mint.morus.v1.Markdown/ToHtml',
        $argument,
        ['\Mint\Morus\V1\MarkdownToHtmlResponse', 'decode'],
        $metadata, $options);
    }

}
