package com.github.saturn_xiv.palm.plugins.lily.v1;

import static io.grpc.MethodDescriptor.generateFullMethodName;

/**
 */
@javax.annotation.Generated(
    value = "by gRPC proto compiler (version 1.59.0)",
    comments = "Source: lily.proto")
@io.grpc.stub.annotations.GrpcGenerated
public final class S3Grpc {

  private S3Grpc() {}

  public static final java.lang.String SERVICE_NAME = "palm.lily.v1.S3";

  // Static method descriptors that strictly reflect the proto.
  private static volatile io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest,
      com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse> getGetFileMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "GetFile",
      requestType = com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest.class,
      responseType = com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest,
      com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse> getGetFileMethod() {
    io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest, com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse> getGetFileMethod;
    if ((getGetFileMethod = S3Grpc.getGetFileMethod) == null) {
      synchronized (S3Grpc.class) {
        if ((getGetFileMethod = S3Grpc.getGetFileMethod) == null) {
          S3Grpc.getGetFileMethod = getGetFileMethod =
              io.grpc.MethodDescriptor.<com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest, com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "GetFile"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse.getDefaultInstance()))
              .setSchemaDescriptor(new S3MethodDescriptorSupplier("GetFile"))
              .build();
        }
      }
    }
    return getGetFileMethod;
  }

  /**
   * Creates a new async stub that supports all call types for the service
   */
  public static S3Stub newStub(io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<S3Stub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<S3Stub>() {
        @java.lang.Override
        public S3Stub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new S3Stub(channel, callOptions);
        }
      };
    return S3Stub.newStub(factory, channel);
  }

  /**
   * Creates a new blocking-style stub that supports unary and streaming output calls on the service
   */
  public static S3BlockingStub newBlockingStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<S3BlockingStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<S3BlockingStub>() {
        @java.lang.Override
        public S3BlockingStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new S3BlockingStub(channel, callOptions);
        }
      };
    return S3BlockingStub.newStub(factory, channel);
  }

  /**
   * Creates a new ListenableFuture-style stub that supports unary calls on the service
   */
  public static S3FutureStub newFutureStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<S3FutureStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<S3FutureStub>() {
        @java.lang.Override
        public S3FutureStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new S3FutureStub(channel, callOptions);
        }
      };
    return S3FutureStub.newStub(factory, channel);
  }

  /**
   */
  public interface AsyncService {

    /**
     */
    default void getFile(com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getGetFileMethod(), responseObserver);
    }
  }

  /**
   * Base class for the server implementation of the service S3.
   */
  public static abstract class S3ImplBase
      implements io.grpc.BindableService, AsyncService {

    @java.lang.Override public final io.grpc.ServerServiceDefinition bindService() {
      return S3Grpc.bindService(this);
    }
  }

  /**
   * A stub to allow clients to do asynchronous rpc calls to service S3.
   */
  public static final class S3Stub
      extends io.grpc.stub.AbstractAsyncStub<S3Stub> {
    private S3Stub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected S3Stub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new S3Stub(channel, callOptions);
    }

    /**
     */
    public void getFile(com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getGetFileMethod(), getCallOptions()), request, responseObserver);
    }
  }

  /**
   * A stub to allow clients to do synchronous rpc calls to service S3.
   */
  public static final class S3BlockingStub
      extends io.grpc.stub.AbstractBlockingStub<S3BlockingStub> {
    private S3BlockingStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected S3BlockingStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new S3BlockingStub(channel, callOptions);
    }

    /**
     */
    public com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse getFile(com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getGetFileMethod(), getCallOptions(), request);
    }
  }

  /**
   * A stub to allow clients to do ListenableFuture-style rpc calls to service S3.
   */
  public static final class S3FutureStub
      extends io.grpc.stub.AbstractFutureStub<S3FutureStub> {
    private S3FutureStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected S3FutureStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new S3FutureStub(channel, callOptions);
    }

    /**
     */
    public com.google.common.util.concurrent.ListenableFuture<com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse> getFile(
        com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getGetFileMethod(), getCallOptions()), request);
    }
  }

  private static final int METHODID_GET_FILE = 0;

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
        case METHODID_GET_FILE:
          serviceImpl.getFile((com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest) request,
              (io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse>) responseObserver);
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
          getGetFileMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileRequest,
              com.github.saturn_xiv.palm.plugins.lily.v1.S3GetFileResponse>(
                service, METHODID_GET_FILE)))
        .build();
  }

  private static abstract class S3BaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoFileDescriptorSupplier, io.grpc.protobuf.ProtoServiceDescriptorSupplier {
    S3BaseDescriptorSupplier() {}

    @java.lang.Override
    public com.google.protobuf.Descriptors.FileDescriptor getFileDescriptor() {
      return com.github.saturn_xiv.palm.plugins.lily.v1.Lily.getDescriptor();
    }

    @java.lang.Override
    public com.google.protobuf.Descriptors.ServiceDescriptor getServiceDescriptor() {
      return getFileDescriptor().findServiceByName("S3");
    }
  }

  private static final class S3FileDescriptorSupplier
      extends S3BaseDescriptorSupplier {
    S3FileDescriptorSupplier() {}
  }

  private static final class S3MethodDescriptorSupplier
      extends S3BaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoMethodDescriptorSupplier {
    private final java.lang.String methodName;

    S3MethodDescriptorSupplier(java.lang.String methodName) {
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
      synchronized (S3Grpc.class) {
        result = serviceDescriptor;
        if (result == null) {
          serviceDescriptor = result = io.grpc.ServiceDescriptor.newBuilder(SERVICE_NAME)
              .setSchemaDescriptor(new S3FileDescriptorSupplier())
              .addMethod(getGetFileMethod())
              .build();
        }
      }
    }
    return result;
  }
}
