<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Palm\Lily\V1;

/**
 */
class TexStub {

    /**
     * @param \Palm\Lily\V1\TexToRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Palm\Lily\V1\S3File for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function ToPdf(
        \Palm\Lily\V1\TexToRequest $request,
        \Grpc\ServerContext $context
    ): ?\Palm\Lily\V1\S3File {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \Palm\Lily\V1\TexToRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Palm\Lily\V1\S3File for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function ToWord(
        \Palm\Lily\V1\TexToRequest $request,
        \Grpc\ServerContext $context
    ): ?\Palm\Lily\V1\S3File {
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
            '/palm.lily.v1.Tex/ToPdf' => new \Grpc\MethodDescriptor(
                $this,
                'ToPdf',
                '\Palm\Lily\V1\TexToRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/palm.lily.v1.Tex/ToWord' => new \Grpc\MethodDescriptor(
                $this,
                'ToWord',
                '\Palm\Lily\V1\TexToRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
        ];
    }

}
