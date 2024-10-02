package com.github.saturn_xiv.palm.plugins.lily.v1;

import static io.grpc.MethodDescriptor.generateFullMethodName;

/**
 */
@javax.annotation.Generated(
    value = "by gRPC proto compiler (version 1.59.0)",
    comments = "Source: lily.proto")
@io.grpc.stub.annotations.GrpcGenerated
public final class EpubGrpc {

  private EpubGrpc() {}

  public static final java.lang.String SERVICE_NAME = "palm.lily.v1.Epub";

  // Static method descriptors that strictly reflect the proto.
  private static volatile io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest,
      com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getBuildMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "Build",
      requestType = com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest.class,
      responseType = com.github.saturn_xiv.palm.plugins.lily.v1.S3File.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest,
      com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getBuildMethod() {
    io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest, com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getBuildMethod;
    if ((getBuildMethod = EpubGrpc.getBuildMethod) == null) {
      synchronized (EpubGrpc.class) {
        if ((getBuildMethod = EpubGrpc.getBuildMethod) == null) {
          EpubGrpc.getBuildMethod = getBuildMethod =
              io.grpc.MethodDescriptor.<com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest, com.github.saturn_xiv.palm.plugins.lily.v1.S3File>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "Build"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.S3File.getDefaultInstance()))
              .setSchemaDescriptor(new EpubMethodDescriptorSupplier("Build"))
              .build();
        }
      }
    }
    return getBuildMethod;
  }

  /**
   * Creates a new async stub that supports all call types for the service
   */
  public static EpubStub newStub(io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<EpubStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<EpubStub>() {
        @java.lang.Override
        public EpubStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new EpubStub(channel, callOptions);
        }
      };
    return EpubStub.newStub(factory, channel);
  }

  /**
   * Creates a new blocking-style stub that supports unary and streaming output calls on the service
   */
  public static EpubBlockingStub newBlockingStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<EpubBlockingStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<EpubBlockingStub>() {
        @java.lang.Override
        public EpubBlockingStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new EpubBlockingStub(channel, callOptions);
        }
      };
    return EpubBlockingStub.newStub(factory, channel);
  }

  /**
   * Creates a new ListenableFuture-style stub that supports unary calls on the service
   */
  public static EpubFutureStub newFutureStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<EpubFutureStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<EpubFutureStub>() {
        @java.lang.Override
        public EpubFutureStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new EpubFutureStub(channel, callOptions);
        }
      };
    return EpubFutureStub.newStub(factory, channel);
  }

  /**
   */
  public interface AsyncService {

    /**
     */
    default void build(com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getBuildMethod(), responseObserver);
    }
  }

  /**
   * Base class for the server implementation of the service Epub.
   */
  public static abstract class EpubImplBase
      implements io.grpc.BindableService, AsyncService {

    @java.lang.Override public final io.grpc.ServerServiceDefinition bindService() {
      return EpubGrpc.bindService(this);
    }
  }

  /**
   * A stub to allow clients to do asynchronous rpc calls to service Epub.
   */
  public static final class EpubStub
      extends io.grpc.stub.AbstractAsyncStub<EpubStub> {
    private EpubStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected EpubStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new EpubStub(channel, callOptions);
    }

    /**
     */
    public void build(com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getBuildMethod(), getCallOptions()), request, responseObserver);
    }
  }

  /**
   * A stub to allow clients to do synchronous rpc calls to service Epub.
   */
  public static final class EpubBlockingStub
      extends io.grpc.stub.AbstractBlockingStub<EpubBlockingStub> {
    private EpubBlockingStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected EpubBlockingStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new EpubBlockingStub(channel, callOptions);
    }

    /**
     */
    public com.github.saturn_xiv.palm.plugins.lily.v1.S3File build(com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getBuildMethod(), getCallOptions(), request);
    }
  }

  /**
   * A stub to allow clients to do ListenableFuture-style rpc calls to service Epub.
   */
  public static final class EpubFutureStub
      extends io.grpc.stub.AbstractFutureStub<EpubFutureStub> {
    private EpubFutureStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected EpubFutureStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new EpubFutureStub(channel, callOptions);
    }

    /**
     */
    public com.google.common.util.concurrent.ListenableFuture<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> build(
        com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getBuildMethod(), getCallOptions()), request);
    }
  }

  private static final int METHODID_BUILD = 0;

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
        case METHODID_BUILD:
          serviceImpl.build((com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest) request,
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
          getBuildMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.github.saturn_xiv.palm.plugins.lily.v1.EpubBuildRequest,
              com.github.saturn_xiv.palm.plugins.lily.v1.S3File>(
                service, METHODID_BUILD)))
        .build();
  }

  private static abstract class EpubBaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoFileDescriptorSupplier, io.grpc.protobuf.ProtoServiceDescriptorSupplier {
    EpubBaseDescriptorSupplier() {}

    @java.lang.Override
    public com.google.protobuf.Descriptors.FileDescriptor getFileDescriptor() {
      return com.github.saturn_xiv.palm.plugins.lily.v1.Lily.getDescriptor();
    }

    @java.lang.Override
    public com.google.protobuf.Descriptors.ServiceDescriptor getServiceDescriptor() {
      return getFileDescriptor().findServiceByName("Epub");
    }
  }

  private static final class EpubFileDescriptorSupplier
      extends EpubBaseDescriptorSupplier {
    EpubFileDescriptorSupplier() {}
  }

  private static final class EpubMethodDescriptorSupplier
      extends EpubBaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoMethodDescriptorSupplier {
    private final java.lang.String methodName;

    EpubMethodDescriptorSupplier(java.lang.String methodName) {
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
      synchronized (EpubGrpc.class) {
        result = serviceDescriptor;
        if (result == null) {
          serviceDescriptor = result = io.grpc.ServiceDescriptor.newBuilder(SERVICE_NAME)
              .setSchemaDescriptor(new EpubFileDescriptorSupplier())
              .addMethod(getBuildMethod())
              .build();
        }
      }
    }
    return result;
  }
}
