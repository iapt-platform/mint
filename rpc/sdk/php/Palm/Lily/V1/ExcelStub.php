<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Palm\Lily\V1;

/**
 * ----------------------------------------------------------------------------
 *
 */
class ExcelStub {

    /**
     * @param \Palm\Lily\V1\S3File $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Palm\Lily\V1\ExcelModel for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function Parse(
        \Palm\Lily\V1\S3File $request,
        \Grpc\ServerContext $context
    ): ?\Palm\Lily\V1\ExcelModel {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \Palm\Lily\V1\ExcelModel $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Palm\Lily\V1\S3File for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function Generate(
        \Palm\Lily\V1\ExcelModel $request,
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
            '/palm.lily.v1.Excel/Parse' => new \Grpc\MethodDescriptor(
                $this,
                'Parse',
                '\Palm\Lily\V1\S3File',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/palm.lily.v1.Excel/Generate' => new \Grpc\MethodDescriptor(
                $this,
                'Generate',
                '\Palm\Lily\V1\ExcelModel',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
        ];
    }

}
