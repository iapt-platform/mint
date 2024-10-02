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
        ];
    }

}
