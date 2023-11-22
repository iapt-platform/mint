package com.github.saturn_xiv.palm.plugins.lily.v1;

import static io.grpc.MethodDescriptor.generateFullMethodName;

/**
 */
@javax.annotation.Generated(
    value = "by gRPC proto compiler (version 1.59.0)",
    comments = "Source: lily.proto")
@io.grpc.stub.annotations.GrpcGenerated
public final class TexGrpc {

  private TexGrpc() {}

  public static final java.lang.String SERVICE_NAME = "palm.lily.v1.Tex";

  // Static method descriptors that strictly reflect the proto.
  private static volatile io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest,
      com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getToPdfMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "ToPdf",
      requestType = com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest.class,
      responseType = com.github.saturn_xiv.palm.plugins.lily.v1.S3File.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest,
      com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getToPdfMethod() {
    io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest, com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getToPdfMethod;
    if ((getToPdfMethod = TexGrpc.getToPdfMethod) == null) {
      synchronized (TexGrpc.class) {
        if ((getToPdfMethod = TexGrpc.getToPdfMethod) == null) {
          TexGrpc.getToPdfMethod = getToPdfMethod =
              io.grpc.MethodDescriptor.<com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest, com.github.saturn_xiv.palm.plugins.lily.v1.S3File>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "ToPdf"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.S3File.getDefaultInstance()))
              .setSchemaDescriptor(new TexMethodDescriptorSupplier("ToPdf"))
              .build();
        }
      }
    }
    return getToPdfMethod;
  }

  private static volatile io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest,
      com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getToWordMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "ToWord",
      requestType = com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest.class,
      responseType = com.github.saturn_xiv.palm.plugins.lily.v1.S3File.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest,
      com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getToWordMethod() {
    io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest, com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getToWordMethod;
    if ((getToWordMethod = TexGrpc.getToWordMethod) == null) {
      synchronized (TexGrpc.class) {
        if ((getToWordMethod = TexGrpc.getToWordMethod) == null) {
          TexGrpc.getToWordMethod = getToWordMethod =
              io.grpc.MethodDescriptor.<com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest, com.github.saturn_xiv.palm.plugins.lily.v1.S3File>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "ToWord"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.S3File.getDefaultInstance()))
              .setSchemaDescriptor(new TexMethodDescriptorSupplier("ToWord"))
              .build();
        }
      }
    }
    return getToWordMethod;
  }

  /**
   * Creates a new async stub that supports all call types for the service
   */
  public static TexStub newStub(io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<TexStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<TexStub>() {
        @java.lang.Override
        public TexStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new TexStub(channel, callOptions);
        }
      };
    return TexStub.newStub(factory, channel);
  }

  /**
   * Creates a new blocking-style stub that supports unary and streaming output calls on the service
   */
  public static TexBlockingStub newBlockingStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<TexBlockingStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<TexBlockingStub>() {
        @java.lang.Override
        public TexBlockingStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new TexBlockingStub(channel, callOptions);
        }
      };
    return TexBlockingStub.newStub(factory, channel);
  }

  /**
   * Creates a new ListenableFuture-style stub that supports unary calls on the service
   */
  public static TexFutureStub newFutureStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<TexFutureStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<TexFutureStub>() {
        @java.lang.Override
        public TexFutureStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new TexFutureStub(channel, callOptions);
        }
      };
    return TexFutureStub.newStub(factory, channel);
  }

  /**
   */
  public interface AsyncService {

    /**
     */
    default void toPdf(com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getToPdfMethod(), responseObserver);
    }

    /**
     */
    default void toWord(com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getToWordMethod(), responseObserver);
    }
  }

  /**
   * Base class for the server implementation of the service Tex.
   */
  public static abstract class TexImplBase
      implements io.grpc.BindableService, AsyncService {

    @java.lang.Override public final io.grpc.ServerServiceDefinition bindService() {
      return TexGrpc.bindService(this);
    }
  }

  /**
   * A stub to allow clients to do asynchronous rpc calls to service Tex.
   */
  public static final class TexStub
      extends io.grpc.stub.AbstractAsyncStub<TexStub> {
    private TexStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected TexStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new TexStub(channel, callOptions);
    }

    /**
     */
    public void toPdf(com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getToPdfMethod(), getCallOptions()), request, responseObserver);
    }

    /**
     */
    public void toWord(com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getToWordMethod(), getCallOptions()), request, responseObserver);
    }
  }

  /**
   * A stub to allow clients to do synchronous rpc calls to service Tex.
   */
  public static final class TexBlockingStub
      extends io.grpc.stub.AbstractBlockingStub<TexBlockingStub> {
    private TexBlockingStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected TexBlockingStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new TexBlockingStub(channel, callOptions);
    }

    /**
     */
    public com.github.saturn_xiv.palm.plugins.lily.v1.S3File toPdf(com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getToPdfMethod(), getCallOptions(), request);
    }

    /**
     */
    public com.github.saturn_xiv.palm.plugins.lily.v1.S3File toWord(com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getToWordMethod(), getCallOptions(), request);
    }
  }

  /**
   * A stub to allow clients to do ListenableFuture-style rpc calls to service Tex.
   */
  public static final class TexFutureStub
      extends io.grpc.stub.AbstractFutureStub<TexFutureStub> {
    private TexFutureStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected TexFutureStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new TexFutureStub(channel, callOptions);
    }

    /**
     */
    public com.google.common.util.concurrent.ListenableFuture<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> toPdf(
        com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getToPdfMethod(), getCallOptions()), request);
    }

    /**
     */
    public com.google.common.util.concurrent.ListenableFuture<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> toWord(
        com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getToWordMethod(), getCallOptions()), request);
    }
  }

  private static final int METHODID_TO_PDF = 0;
  private static final int METHODID_TO_WORD = 1;

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
        case METHODID_TO_PDF:
          serviceImpl.toPdf((com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest) request,
              (io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3File>) responseObserver);
          break;
        case METHODID_TO_WORD:
          serviceImpl.toWord((com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest) request,
              (io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3File>) responseObserver);
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
          getToPdfMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest,
              com.github.saturn_xiv.palm.plugins.lily.v1.S3File>(
                service, METHODID_TO_PDF)))
        .addMethod(
          getToWordMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.github.saturn_xiv.palm.plugins.lily.v1.TexToRequest,
              com.github.saturn_xiv.palm.plugins.lily.v1.S3File>(
                service, METHODID_TO_WORD)))
        .build();
  }

  private static abstract class TexBaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoFileDescriptorSupplier, io.grpc.protobuf.ProtoServiceDescriptorSupplier {
    TexBaseDescriptorSupplier() {}

    @java.lang.Override
    public com.google.protobuf.Descriptors.FileDescriptor getFileDescriptor() {
      return com.github.saturn_xiv.palm.plugins.lily.v1.Lily.getDescriptor();
    }

    @java.lang.Override
    public com.google.protobuf.Descriptors.ServiceDescriptor getServiceDescriptor() {
      return getFileDescriptor().findServiceByName("Tex");
    }
  }

  private static final class TexFileDescriptorSupplier
      extends TexBaseDescriptorSupplier {
    TexFileDescriptorSupplier() {}
  }

  private static final class TexMethodDescriptorSupplier
      extends TexBaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoMethodDescriptorSupplier {
    private final java.lang.String methodName;

    TexMethodDescriptorSupplier(java.lang.String methodName) {
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
      synchronized (TexGrpc.class) {
        result = serviceDescriptor;
        if (result == null) {
          serviceDescriptor = result = io.grpc.ServiceDescriptor.newBuilder(SERVICE_NAME)
              .setSchemaDescriptor(new TexFileDescriptorSupplier())
              .addMethod(getToPdfMethod())
              .addMethod(getToWordMethod())
              .build();
        }
      }
    }
    return result;
  }
}
