// GENERATED CODE -- DO NOT EDIT!

'use strict';
var grpc = require('@grpc/grpc-js');
var morus_pb = require('./morus_pb.js');

function serialize_mint_morus_v1_MarkdownToHtmlRequest(arg) {
  if (!(arg instanceof morus_pb.MarkdownToHtmlRequest)) {
    throw new Error('Expected argument of type mint.morus.v1.MarkdownToHtmlRequest');
  }
  return Buffer.from(arg.serializeBinary());
}

function deserialize_mint_morus_v1_MarkdownToHtmlRequest(buffer_arg) {
  return morus_pb.MarkdownToHtmlRequest.deserializeBinary(new Uint8Array(buffer_arg));
}

function serialize_mint_morus_v1_MarkdownToHtmlResponse(arg) {
  if (!(arg instanceof morus_pb.MarkdownToHtmlResponse)) {
    throw new Error('Expected argument of type mint.morus.v1.MarkdownToHtmlResponse');
  }
  return Buffer.from(arg.serializeBinary());
}

function deserialize_mint_morus_v1_MarkdownToHtmlResponse(buffer_arg) {
  return morus_pb.MarkdownToHtmlResponse.deserializeBinary(new Uint8Array(buffer_arg));
}


// ----------------------------------------------------------------------------
var MarkdownService = exports.MarkdownService = {
  toHtml: {
    path: '/mint.morus.v1.Markdown/ToHtml',
    requestStream: false,
    responseStream: false,
    requestType: morus_pb.MarkdownToHtmlRequest,
    responseType: morus_pb.MarkdownToHtmlResponse,
    requestSerialize: serialize_mint_morus_v1_MarkdownToHtmlRequest,
    requestDeserialize: deserialize_mint_morus_v1_MarkdownToHtmlRequest,
    responseSerialize: serialize_mint_morus_v1_MarkdownToHtmlResponse,
    responseDeserialize: deserialize_mint_morus_v1_MarkdownToHtmlResponse,
  },
};

exports.MarkdownClient = grpc.makeGenericClientConstructor(MarkdownService);
