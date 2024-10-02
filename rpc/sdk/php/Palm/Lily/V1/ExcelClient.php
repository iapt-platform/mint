<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Palm\Lily\V1;

/**
 * ----------------------------------------------------------------------------
 *
 */
class ExcelClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Palm\Lily\V1\S3File $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Parse(\Palm\Lily\V1\S3File $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/palm.lily.v1.Excel/Parse',
        $argument,
        ['\Palm\Lily\V1\ExcelModel', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Palm\Lily\V1\ExcelModel $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Generate(\Palm\Lily\V1\ExcelModel $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/palm.lily.v1.Excel/Generate',
        $argument,
        ['\Palm\Lily\V1\S3File', 'decode'],
        $metadata, $options);
    }

}
