<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Palm\Lily\V1;

/**
 */
class S3Stub {

    /**
     * @param \Palm\Lily\V1\S3GetFileRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Palm\Lily\V1\S3GetFileResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function GetFile(
        \Palm\Lily\V1\S3GetFileRequest $request,
        \Grpc\ServerContext $context
    ): ?\Palm\Lily\V1\S3GetFileResponse {
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
            '/palm.lily.v1.S3/GetFile' => new \Grpc\MethodDescriptor(
                $this,
                'GetFile',
                '\Palm\Lily\V1\S3GetFileRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
        ];
    }

}
