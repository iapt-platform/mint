// Generated by the protocol buffer compiler.  DO NOT EDIT!
// source: lily.proto

package com.github.saturn_xiv.palm.plugins.lily.v1;

public interface FileOrBuilder extends
    // @@protoc_insertion_point(interface_extends:palm.lily.v1.File)
    com.google.protobuf.MessageOrBuilder {

  /**
   * <code>optional string content_type = 1;</code>
   * @return Whether the contentType field is set.
   */
  boolean hasContentType();
  /**
   * <code>optional string content_type = 1;</code>
   * @return The contentType.
   */
  java.lang.String getContentType();
  /**
   * <code>optional string content_type = 1;</code>
   * @return The bytes for contentType.
   */
  com.google.protobuf.ByteString
      getContentTypeBytes();

  /**
   * <code>bytes payload = 2;</code>
   * @return The payload.
   */
  com.google.protobuf.ByteString getPayload();
}