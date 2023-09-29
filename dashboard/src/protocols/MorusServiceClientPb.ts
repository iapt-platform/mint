/**
 * @fileoverview gRPC-Web generated client stub for mint.morus.v1
 * @enhanceable
 * @public
 */

// Code generated by protoc-gen-grpc-web. DO NOT EDIT.
// versions:
// 	protoc-gen-grpc-web v1.4.2
// 	protoc              v4.23.4
// source: morus.proto


/* eslint-disable */
// @ts-nocheck


import * as grpcWeb from 'grpc-web';

import * as morus_pb from './morus_pb';


export class MarkdownClient {
  client_: grpcWeb.AbstractClientBase;
  hostname_: string;
  credentials_: null | { [index: string]: string; };
  options_: null | { [index: string]: any; };

  constructor (hostname: string,
               credentials?: null | { [index: string]: string; },
               options?: null | { [index: string]: any; }) {
    if (!options) options = {};
    if (!credentials) credentials = {};
    options['format'] = 'binary';

    this.client_ = new grpcWeb.GrpcWebClientBase(options);
    this.hostname_ = hostname.replace(/\/+$/, '');
    this.credentials_ = credentials;
    this.options_ = options;
  }

  methodDescriptorToHtml = new grpcWeb.MethodDescriptor(
    '/mint.morus.v1.Markdown/ToHtml',
    grpcWeb.MethodType.UNARY,
    morus_pb.MarkdownToHtmlRequest,
    morus_pb.MarkdownToHtmlResponse,
    (request: morus_pb.MarkdownToHtmlRequest) => {
      return request.serializeBinary();
    },
    morus_pb.MarkdownToHtmlResponse.deserializeBinary
  );

  toHtml(
    request: morus_pb.MarkdownToHtmlRequest,
    metadata: grpcWeb.Metadata | null): Promise<morus_pb.MarkdownToHtmlResponse>;

  toHtml(
    request: morus_pb.MarkdownToHtmlRequest,
    metadata: grpcWeb.Metadata | null,
    callback: (err: grpcWeb.RpcError,
               response: morus_pb.MarkdownToHtmlResponse) => void): grpcWeb.ClientReadableStream<morus_pb.MarkdownToHtmlResponse>;

  toHtml(
    request: morus_pb.MarkdownToHtmlRequest,
    metadata: grpcWeb.Metadata | null,
    callback?: (err: grpcWeb.RpcError,
               response: morus_pb.MarkdownToHtmlResponse) => void) {
    if (callback !== undefined) {
      return this.client_.rpcCall(
        this.hostname_ +
          '/mint.morus.v1.Markdown/ToHtml',
        request,
        metadata || {},
        this.methodDescriptorToHtml,
        callback);
    }
    return this.client_.unaryCall(
    this.hostname_ +
      '/mint.morus.v1.Markdown/ToHtml',
    request,
    metadata || {},
    this.methodDescriptorToHtml);
  }

}

