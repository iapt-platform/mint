<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Palm\Lily\V1;

/**
 */
class TexClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Palm\Lily\V1\TexToRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ToPdf(\Palm\Lily\V1\TexToRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/palm.lily.v1.Tex/ToPdf',
        $argument,
        ['\Palm\Lily\V1\S3File', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Palm\Lily\V1\TexToRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ToWord(\Palm\Lily\V1\TexToRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/palm.lily.v1.Tex/ToWord',
        $argument,
        ['\Palm\Lily\V1\S3File', 'decode'],
        $metadata, $options);
    }

}
