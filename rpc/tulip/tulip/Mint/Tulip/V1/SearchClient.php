<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Mint\Tulip\V1;

/**
 */
class SearchClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Mint\Tulip\V1\SearchRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Pali(\Mint\Tulip\V1\SearchRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/mint.tulip.v1.Search/Pali',
        $argument,
        ['\Mint\Tulip\V1\SearchResponse', 'decode'],
        $metadata, $options);
    }

}
