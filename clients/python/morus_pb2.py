# -*- coding: utf-8 -*-
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: morus.proto
"""Generated protocol buffer code."""
from google.protobuf import descriptor as _descriptor
from google.protobuf import descriptor_pool as _descriptor_pool
from google.protobuf import symbol_database as _symbol_database
from google.protobuf.internal import builder as _builder
# @@protoc_insertion_point(imports)

_sym_db = _symbol_database.Default()




DESCRIPTOR = _descriptor_pool.Default().AddSerializedFile(b'\n\x0bmorus.proto\x12\rmint.morus.v1\":\n\x15MarkdownToHtmlRequest\x12\x0f\n\x07payload\x18\x01 \x01(\t\x12\x10\n\x08sanitize\x18\x02 \x01(\x08\")\n\x16MarkdownToHtmlResponse\x12\x0f\n\x07payload\x18\x01 \x01(\t2c\n\x08Markdown\x12W\n\x06ToHtml\x12$.mint.morus.v1.MarkdownToHtmlRequest\x1a%.mint.morus.v1.MarkdownToHtmlResponse\"\x00\x42\x32\n.com.github.iapt_platform.mint.plugins.morus.v1P\x01\x62\x06proto3')

_globals = globals()
_builder.BuildMessageAndEnumDescriptors(DESCRIPTOR, _globals)
_builder.BuildTopDescriptorsAndMessages(DESCRIPTOR, 'morus_pb2', _globals)
if _descriptor._USE_C_DESCRIPTORS == False:

  DESCRIPTOR._options = None
  DESCRIPTOR._serialized_options = b'\n.com.github.iapt_platform.mint.plugins.morus.v1P\001'
  _globals['_MARKDOWNTOHTMLREQUEST']._serialized_start=30
  _globals['_MARKDOWNTOHTMLREQUEST']._serialized_end=88
  _globals['_MARKDOWNTOHTMLRESPONSE']._serialized_start=90
  _globals['_MARKDOWNTOHTMLRESPONSE']._serialized_end=131
  _globals['_MARKDOWN']._serialized_start=133
  _globals['_MARKDOWN']._serialized_end=232
# @@protoc_insertion_point(module_scope)