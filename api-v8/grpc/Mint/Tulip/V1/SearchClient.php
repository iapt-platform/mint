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

    /**
     * @param \Mint\Tulip\V1\SearchRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function BookList(\Mint\Tulip\V1\SearchRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/mint.tulip.v1.Search/BookList',
        $argument,
        ['\Mint\Tulip\V1\BookListResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Mint\Tulip\V1\UpdateRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Update(\Mint\Tulip\V1\UpdateRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/mint.tulip.v1.Search/Update',
        $argument,
        ['\Mint\Tulip\V1\UpdateResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Mint\Tulip\V1\UpdateIndexRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function UpdateIndex(\Mint\Tulip\V1\UpdateIndexRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/mint.tulip.v1.Search/UpdateIndex',
        $argument,
        ['\Mint\Tulip\V1\UpdateIndexResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Mint\Tulip\V1\UploadDictionaryRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function UploadDictionary(\Mint\Tulip\V1\UploadDictionaryRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/mint.tulip.v1.Search/UploadDictionary',
        $argument,
        ['\Mint\Tulip\V1\UploadDictionaryResponse', 'decode'],
        $metadata, $options);
    }

}
