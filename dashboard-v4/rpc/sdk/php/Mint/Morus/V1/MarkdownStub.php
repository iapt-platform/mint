<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Mint\Morus\V1;

/**
 * ----------------------------------------------------------------------------
 */
class MarkdownStub {

    /**
     * @param \Mint\Morus\V1\MarkdownToHtmlRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Mint\Morus\V1\MarkdownToHtmlResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function ToHtml(
        \Mint\Morus\V1\MarkdownToHtmlRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Morus\V1\MarkdownToHtmlResponse {
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
            '/mint.morus.v1.Markdown/ToHtml' => new \Grpc\MethodDescriptor(
                $this,
                'ToHtml',
                '\Mint\Morus\V1\MarkdownToHtmlRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
        ];
    }

}
