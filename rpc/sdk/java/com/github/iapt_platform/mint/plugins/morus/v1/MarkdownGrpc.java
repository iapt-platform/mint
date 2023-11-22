package com.github.iapt_platform.mint.plugins.morus.v1;

import static io.grpc.MethodDescriptor.generateFullMethodName;

/**
 * <pre>
 * ----------------------------------------------------------------------------
 * </pre>
 */
@javax.annotation.Generated(
    value = "by gRPC proto compiler (version 1.59.0)",
    comments = "Source: morus.proto")
@io.grpc.stub.annotations.GrpcGenerated
public final class MarkdownGrpc {

  private MarkdownGrpc() {}

  public static final java.lang.String SERVICE_NAME = "mint.morus.v1.Markdown";

  // Static method descriptors that strictly reflect the proto.
  private static volatile io.grpc.MethodDescriptor<com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest,
      com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse> getToHtmlMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "ToHtml",
      requestType = com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest.class,
      responseType = com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest,
      com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse> getToHtmlMethod() {
    io.grpc.MethodDescriptor<com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest, com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse> getToHtmlMethod;
    if ((getToHtmlMethod = MarkdownGrpc.getToHtmlMethod) == null) {
      synchronized (MarkdownGrpc.class) {
        if ((getToHtmlMethod = MarkdownGrpc.getToHtmlMethod) == null) {
          MarkdownGrpc.getToHtmlMethod = getToHtmlMethod =
              io.grpc.MethodDescriptor.<com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest, com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "ToHtml"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse.getDefaultInstance()))
              .setSchemaDescriptor(new MarkdownMethodDescriptorSupplier("ToHtml"))
              .build();
        }
      }
    }
    return getToHtmlMethod;
  }

  /**
   * Creates a new async stub that supports all call types for the service
   */
  public static MarkdownStub newStub(io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<MarkdownStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<MarkdownStub>() {
        @java.lang.Override
        public MarkdownStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new MarkdownStub(channel, callOptions);
        }
      };
    return MarkdownStub.newStub(factory, channel);
  }

  /**
   * Creates a new blocking-style stub that supports unary and streaming output calls on the service
   */
  public static MarkdownBlockingStub newBlockingStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<MarkdownBlockingStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<MarkdownBlockingStub>() {
        @java.lang.Override
        public MarkdownBlockingStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new MarkdownBlockingStub(channel, callOptions);
        }
      };
    return MarkdownBlockingStub.newStub(factory, channel);
  }

  /**
   * Creates a new ListenableFuture-style stub that supports unary calls on the service
   */
  public static MarkdownFutureStub newFutureStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<MarkdownFutureStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<MarkdownFutureStub>() {
        @java.lang.Override
        public MarkdownFutureStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new MarkdownFutureStub(channel, callOptions);
        }
      };
    return MarkdownFutureStub.newStub(factory, channel);
  }

  /**
   * <pre>
   * ----------------------------------------------------------------------------
   * </pre>
   */
  public interface AsyncService {

    /**
     */
    default void toHtml(com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest request,
        io.grpc.stub.StreamObserver<com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getToHtmlMethod(), responseObserver);
    }
  }

  /**
   * Base class for the server implementation of the service Markdown.
   * <pre>
   * ----------------------------------------------------------------------------
   * </pre>
   */
  public static abstract class MarkdownImplBase
      implements io.grpc.BindableService, AsyncService {

    @java.lang.Override public final io.grpc.ServerServiceDefinition bindService() {
      return MarkdownGrpc.bindService(this);
    }
  }

  /**
   * A stub to allow clients to do asynchronous rpc calls to service Markdown.
   * <pre>
   * ----------------------------------------------------------------------------
   * </pre>
   */
  public static final class MarkdownStub
      extends io.grpc.stub.AbstractAsyncStub<MarkdownStub> {
    private MarkdownStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected MarkdownStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new MarkdownStub(channel, callOptions);
    }

    /**
     */
    public void toHtml(com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest request,
        io.grpc.stub.StreamObserver<com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getToHtmlMethod(), getCallOptions()), request, responseObserver);
    }
  }

  /**
   * A stub to allow clients to do synchronous rpc calls to service Markdown.
   * <pre>
   * ----------------------------------------------------------------------------
   * </pre>
   */
  public static final class MarkdownBlockingStub
      extends io.grpc.stub.AbstractBlockingStub<MarkdownBlockingStub> {
    private MarkdownBlockingStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected MarkdownBlockingStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new MarkdownBlockingStub(channel, callOptions);
    }

    /**
     */
    public com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse toHtml(com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getToHtmlMethod(), getCallOptions(), request);
    }
  }

  /**
   * A stub to allow clients to do ListenableFuture-style rpc calls to service Markdown.
   * <pre>
   * ----------------------------------------------------------------------------
   * </pre>
   */
  public static final class MarkdownFutureStub
      extends io.grpc.stub.AbstractFutureStub<MarkdownFutureStub> {
    private MarkdownFutureStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected MarkdownFutureStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new MarkdownFutureStub(channel, callOptions);
    }

    /**
     */
    public com.google.common.util.concurrent.ListenableFuture<com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse> toHtml(
        com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getToHtmlMethod(), getCallOptions()), request);
    }
  }

  private static final int METHODID_TO_HTML = 0;

  private static final class MethodHandlers<Req, Resp> implements
      io.grpc.stub.ServerCalls.UnaryMethod<Req, Resp>,
      io.grpc.stub.ServerCalls.ServerStreamingMethod<Req, Resp>,
      io.grpc.stub.ServerCalls.ClientStreamingMethod<Req, Resp>,
      io.grpc.stub.ServerCalls.BidiStreamingMethod<Req, Resp> {
    private final AsyncService serviceImpl;
    private final int methodId;

    MethodHandlers(AsyncService serviceImpl, int methodId) {
      this.serviceImpl = serviceImpl;
      this.methodId = methodId;
    }

    @java.lang.Override
    @java.lang.SuppressWarnings("unchecked")
    public void invoke(Req request, io.grpc.stub.StreamObserver<Resp> responseObserver) {
      switch (methodId) {
        case METHODID_TO_HTML:
          serviceImpl.toHtml((com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest) request,
              (io.grpc.stub.StreamObserver<com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse>) responseObserver);
          break;
        default:
          throw new AssertionError();
      }
    }

    @java.lang.Override
    @java.lang.SuppressWarnings("unchecked")
    public io.grpc.stub.StreamObserver<Req> invoke(
        io.grpc.stub.StreamObserver<Resp> responseObserver) {
      switch (methodId) {
        default:
          throw new AssertionError();
      }
    }
  }

  public static final io.grpc.ServerServiceDefinition bindService(AsyncService service) {
    return io.grpc.ServerServiceDefinition.builder(getServiceDescriptor())
        .addMethod(
          getToHtmlMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlRequest,
              com.github.iapt_platform.mint.plugins.morus.v1.MarkdownToHtmlResponse>(
                service, METHODID_TO_HTML)))
        .build();
  }

  private static abstract class MarkdownBaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoFileDescriptorSupplier, io.grpc.protobuf.ProtoServiceDescriptorSupplier {
    MarkdownBaseDescriptorSupplier() {}

    @java.lang.Override
    public com.google.protobuf.Descriptors.FileDescriptor getFileDescriptor() {
      return com.github.iapt_platform.mint.plugins.morus.v1.Morus.getDescriptor();
    }

    @java.lang.Override
    public com.google.protobuf.Descriptors.ServiceDescriptor getServiceDescriptor() {
      return getFileDescriptor().findServiceByName("Markdown");
    }
  }

  private static final class MarkdownFileDescriptorSupplier
      extends MarkdownBaseDescriptorSupplier {
    MarkdownFileDescriptorSupplier() {}
  }

  private static final class MarkdownMethodDescriptorSupplier
      extends MarkdownBaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoMethodDescriptorSupplier {
    private final java.lang.String methodName;

    MarkdownMethodDescriptorSupplier(java.lang.String methodName) {
      this.methodName = methodName;
    }

    @java.lang.Override
    public com.google.protobuf.Descriptors.MethodDescriptor getMethodDescriptor() {
      return getServiceDescriptor().findMethodByName(methodName);
    }
  }

  private static volatile io.grpc.ServiceDescriptor serviceDescriptor;

  public static io.grpc.ServiceDescriptor getServiceDescriptor() {
    io.grpc.ServiceDescriptor result = serviceDescriptor;
    if (result == null) {
      synchronized (MarkdownGrpc.class) {
        result = serviceDescriptor;
        if (result == null) {
          serviceDescriptor = result = io.grpc.ServiceDescriptor.newBuilder(SERVICE_NAME)
              .setSchemaDescriptor(new MarkdownFileDescriptorSupplier())
              .addMethod(getToHtmlMethod())
              .build();
        }
      }
    }
    return result;
  }
}
