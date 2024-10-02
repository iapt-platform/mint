<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Palm\Lily\V1;

/**
 * ----------------------------------------------------------------------------
 *
 */
class TeXLiveClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Palm\Lily\V1\TeXLiveRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ToPdf(\Palm\Lily\V1\TeXLiveRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/palm.lily.v1.TeXLive/ToPdf',
        $argument,
        ['\Palm\Lily\V1\TeXLiveResponse', 'decode'],
        $metadata, $options);
    }

}
