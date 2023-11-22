package com.github.saturn_xiv.palm.plugins.lily.v1;

import static io.grpc.MethodDescriptor.generateFullMethodName;

/**
 */
@javax.annotation.Generated(
    value = "by gRPC proto compiler (version 1.59.0)",
    comments = "Source: lily.proto")
@io.grpc.stub.annotations.GrpcGenerated
public final class ExcelGrpc {

  private ExcelGrpc() {}

  public static final java.lang.String SERVICE_NAME = "palm.lily.v1.Excel";

  // Static method descriptors that strictly reflect the proto.
  private static volatile io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.S3File,
      com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel> getParseMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "Parse",
      requestType = com.github.saturn_xiv.palm.plugins.lily.v1.S3File.class,
      responseType = com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.S3File,
      com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel> getParseMethod() {
    io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.S3File, com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel> getParseMethod;
    if ((getParseMethod = ExcelGrpc.getParseMethod) == null) {
      synchronized (ExcelGrpc.class) {
        if ((getParseMethod = ExcelGrpc.getParseMethod) == null) {
          ExcelGrpc.getParseMethod = getParseMethod =
              io.grpc.MethodDescriptor.<com.github.saturn_xiv.palm.plugins.lily.v1.S3File, com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "Parse"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.S3File.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel.getDefaultInstance()))
              .setSchemaDescriptor(new ExcelMethodDescriptorSupplier("Parse"))
              .build();
        }
      }
    }
    return getParseMethod;
  }

  private static volatile io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel,
      com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getGenerateMethod;

  @io.grpc.stub.annotations.RpcMethod(
      fullMethodName = SERVICE_NAME + '/' + "Generate",
      requestType = com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel.class,
      responseType = com.github.saturn_xiv.palm.plugins.lily.v1.S3File.class,
      methodType = io.grpc.MethodDescriptor.MethodType.UNARY)
  public static io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel,
      com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getGenerateMethod() {
    io.grpc.MethodDescriptor<com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel, com.github.saturn_xiv.palm.plugins.lily.v1.S3File> getGenerateMethod;
    if ((getGenerateMethod = ExcelGrpc.getGenerateMethod) == null) {
      synchronized (ExcelGrpc.class) {
        if ((getGenerateMethod = ExcelGrpc.getGenerateMethod) == null) {
          ExcelGrpc.getGenerateMethod = getGenerateMethod =
              io.grpc.MethodDescriptor.<com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel, com.github.saturn_xiv.palm.plugins.lily.v1.S3File>newBuilder()
              .setType(io.grpc.MethodDescriptor.MethodType.UNARY)
              .setFullMethodName(generateFullMethodName(SERVICE_NAME, "Generate"))
              .setSampledToLocalTracing(true)
              .setRequestMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel.getDefaultInstance()))
              .setResponseMarshaller(io.grpc.protobuf.ProtoUtils.marshaller(
                  com.github.saturn_xiv.palm.plugins.lily.v1.S3File.getDefaultInstance()))
              .setSchemaDescriptor(new ExcelMethodDescriptorSupplier("Generate"))
              .build();
        }
      }
    }
    return getGenerateMethod;
  }

  /**
   * Creates a new async stub that supports all call types for the service
   */
  public static ExcelStub newStub(io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<ExcelStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<ExcelStub>() {
        @java.lang.Override
        public ExcelStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new ExcelStub(channel, callOptions);
        }
      };
    return ExcelStub.newStub(factory, channel);
  }

  /**
   * Creates a new blocking-style stub that supports unary and streaming output calls on the service
   */
  public static ExcelBlockingStub newBlockingStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<ExcelBlockingStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<ExcelBlockingStub>() {
        @java.lang.Override
        public ExcelBlockingStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new ExcelBlockingStub(channel, callOptions);
        }
      };
    return ExcelBlockingStub.newStub(factory, channel);
  }

  /**
   * Creates a new ListenableFuture-style stub that supports unary calls on the service
   */
  public static ExcelFutureStub newFutureStub(
      io.grpc.Channel channel) {
    io.grpc.stub.AbstractStub.StubFactory<ExcelFutureStub> factory =
      new io.grpc.stub.AbstractStub.StubFactory<ExcelFutureStub>() {
        @java.lang.Override
        public ExcelFutureStub newStub(io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
          return new ExcelFutureStub(channel, callOptions);
        }
      };
    return ExcelFutureStub.newStub(factory, channel);
  }

  /**
   */
  public interface AsyncService {

    /**
     */
    default void parse(com.github.saturn_xiv.palm.plugins.lily.v1.S3File request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getParseMethod(), responseObserver);
    }

    /**
     */
    default void generate(com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> responseObserver) {
      io.grpc.stub.ServerCalls.asyncUnimplementedUnaryCall(getGenerateMethod(), responseObserver);
    }
  }

  /**
   * Base class for the server implementation of the service Excel.
   */
  public static abstract class ExcelImplBase
      implements io.grpc.BindableService, AsyncService {

    @java.lang.Override public final io.grpc.ServerServiceDefinition bindService() {
      return ExcelGrpc.bindService(this);
    }
  }

  /**
   * A stub to allow clients to do asynchronous rpc calls to service Excel.
   */
  public static final class ExcelStub
      extends io.grpc.stub.AbstractAsyncStub<ExcelStub> {
    private ExcelStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected ExcelStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new ExcelStub(channel, callOptions);
    }

    /**
     */
    public void parse(com.github.saturn_xiv.palm.plugins.lily.v1.S3File request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getParseMethod(), getCallOptions()), request, responseObserver);
    }

    /**
     */
    public void generate(com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel request,
        io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> responseObserver) {
      io.grpc.stub.ClientCalls.asyncUnaryCall(
          getChannel().newCall(getGenerateMethod(), getCallOptions()), request, responseObserver);
    }
  }

  /**
   * A stub to allow clients to do synchronous rpc calls to service Excel.
   */
  public static final class ExcelBlockingStub
      extends io.grpc.stub.AbstractBlockingStub<ExcelBlockingStub> {
    private ExcelBlockingStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected ExcelBlockingStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new ExcelBlockingStub(channel, callOptions);
    }

    /**
     */
    public com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel parse(com.github.saturn_xiv.palm.plugins.lily.v1.S3File request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getParseMethod(), getCallOptions(), request);
    }

    /**
     */
    public com.github.saturn_xiv.palm.plugins.lily.v1.S3File generate(com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel request) {
      return io.grpc.stub.ClientCalls.blockingUnaryCall(
          getChannel(), getGenerateMethod(), getCallOptions(), request);
    }
  }

  /**
   * A stub to allow clients to do ListenableFuture-style rpc calls to service Excel.
   */
  public static final class ExcelFutureStub
      extends io.grpc.stub.AbstractFutureStub<ExcelFutureStub> {
    private ExcelFutureStub(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      super(channel, callOptions);
    }

    @java.lang.Override
    protected ExcelFutureStub build(
        io.grpc.Channel channel, io.grpc.CallOptions callOptions) {
      return new ExcelFutureStub(channel, callOptions);
    }

    /**
     */
    public com.google.common.util.concurrent.ListenableFuture<com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel> parse(
        com.github.saturn_xiv.palm.plugins.lily.v1.S3File request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getParseMethod(), getCallOptions()), request);
    }

    /**
     */
    public com.google.common.util.concurrent.ListenableFuture<com.github.saturn_xiv.palm.plugins.lily.v1.S3File> generate(
        com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel request) {
      return io.grpc.stub.ClientCalls.futureUnaryCall(
          getChannel().newCall(getGenerateMethod(), getCallOptions()), request);
    }
  }

  private static final int METHODID_PARSE = 0;
  private static final int METHODID_GENERATE = 1;

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
        case METHODID_PARSE:
          serviceImpl.parse((com.github.saturn_xiv.palm.plugins.lily.v1.S3File) request,
              (io.grpc.stub.StreamObserver<com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel>) responseObserver);
          break;
        case METHODID_GENERATE:
          serviceImpl.generate((com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel) request,
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
          getParseMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.github.saturn_xiv.palm.plugins.lily.v1.S3File,
              com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel>(
                service, METHODID_PARSE)))
        .addMethod(
          getGenerateMethod(),
          io.grpc.stub.ServerCalls.asyncUnaryCall(
            new MethodHandlers<
              com.github.saturn_xiv.palm.plugins.lily.v1.ExcelModel,
              com.github.saturn_xiv.palm.plugins.lily.v1.S3File>(
                service, METHODID_GENERATE)))
        .build();
  }

  private static abstract class ExcelBaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoFileDescriptorSupplier, io.grpc.protobuf.ProtoServiceDescriptorSupplier {
    ExcelBaseDescriptorSupplier() {}

    @java.lang.Override
    public com.google.protobuf.Descriptors.FileDescriptor getFileDescriptor() {
      return com.github.saturn_xiv.palm.plugins.lily.v1.Lily.getDescriptor();
    }

    @java.lang.Override
    public com.google.protobuf.Descriptors.ServiceDescriptor getServiceDescriptor() {
      return getFileDescriptor().findServiceByName("Excel");
    }
  }

  private static final class ExcelFileDescriptorSupplier
      extends ExcelBaseDescriptorSupplier {
    ExcelFileDescriptorSupplier() {}
  }

  private static final class ExcelMethodDescriptorSupplier
      extends ExcelBaseDescriptorSupplier
      implements io.grpc.protobuf.ProtoMethodDescriptorSupplier {
    private final java.lang.String methodName;

    ExcelMethodDescriptorSupplier(java.lang.String methodName) {
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
      synchronized (ExcelGrpc.class) {
        result = serviceDescriptor;
        if (result == null) {
          serviceDescriptor = result = io.grpc.ServiceDescriptor.newBuilder(SERVICE_NAME)
              .setSchemaDescriptor(new ExcelFileDescriptorSupplier())
              .addMethod(getParseMethod())
              .addMethod(getGenerateMethod())
              .build();
        }
      }
    }
    return result;
  }
}
