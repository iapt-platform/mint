<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Palm\Lily\V1;

/**
 */
class S3Client extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Palm\Lily\V1\S3GetFileRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetFile(\Palm\Lily\V1\S3GetFileRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/palm.lily.v1.S3/GetFile',
        $argument,
        ['\Palm\Lily\V1\S3GetFileResponse', 'decode'],
        $metadata, $options);
    }

}
