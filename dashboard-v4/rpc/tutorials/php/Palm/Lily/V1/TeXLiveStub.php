<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Palm\Lily\V1;

/**
 * ----------------------------------------------------------------------------
 *
 */
class TeXLiveStub {

    /**
     * @param \Palm\Lily\V1\TeXLiveRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Palm\Lily\V1\TeXLiveResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function ToPdf(
        \Palm\Lily\V1\TeXLiveRequest $request,
        \Grpc\ServerContext $context
    ): ?\Palm\Lily\V1\TeXLiveResponse {
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
            '/palm.lily.v1.TeXLive/ToPdf' => new \Grpc\MethodDescriptor(
                $this,
                'ToPdf',
                '\Palm\Lily\V1\TeXLiveRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
        ];
    }

}
