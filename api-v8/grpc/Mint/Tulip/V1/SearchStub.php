<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Mint\Tulip\V1;

/**
 */
class SearchStub {

    /**
     * @param \Mint\Tulip\V1\SearchRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Mint\Tulip\V1\SearchResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function Pali(
        \Mint\Tulip\V1\SearchRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Tulip\V1\SearchResponse {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \Mint\Tulip\V1\SearchRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Mint\Tulip\V1\BookListResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function BookList(
        \Mint\Tulip\V1\SearchRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Tulip\V1\BookListResponse {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \Mint\Tulip\V1\UpdateRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Mint\Tulip\V1\UpdateResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function Update(
        \Mint\Tulip\V1\UpdateRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Tulip\V1\UpdateResponse {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \Mint\Tulip\V1\UpdateIndexRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Mint\Tulip\V1\UpdateIndexResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function UpdateIndex(
        \Mint\Tulip\V1\UpdateIndexRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Tulip\V1\UpdateIndexResponse {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \Mint\Tulip\V1\UploadDictionaryRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Mint\Tulip\V1\UploadDictionaryResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function UploadDictionary(
        \Mint\Tulip\V1\UploadDictionaryRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Tulip\V1\UploadDictionaryResponse {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * Get the method descriptors of the service for server registration
     *
     * @return array of \Grpc\MethodDescriptor for the service methods
     */
    public final function getMethodDescriptors(): array
    {
        return [
            '/mint.tulip.v1.Search/Pali' => new \Grpc\MethodDescriptor(
                $this,
                'Pali',
                '\Mint\Tulip\V1\SearchRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/mint.tulip.v1.Search/BookList' => new \Grpc\MethodDescriptor(
                $this,
                'BookList',
                '\Mint\Tulip\V1\SearchRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/mint.tulip.v1.Search/Update' => new \Grpc\MethodDescriptor(
                $this,
                'Update',
                '\Mint\Tulip\V1\UpdateRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/mint.tulip.v1.Search/UpdateIndex' => new \Grpc\MethodDescriptor(
                $this,
                'UpdateIndex',
                '\Mint\Tulip\V1\UpdateIndexRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/mint.tulip.v1.Search/UploadDictionary' => new \Grpc\MethodDescriptor(
                $this,
                'UploadDictionary',
                '\Mint\Tulip\V1\UploadDictionaryRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
        ];
    }

}
