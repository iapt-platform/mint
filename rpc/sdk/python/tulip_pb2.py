# -*- coding: utf-8 -*-
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: tulip.proto
"""Generated protocol buffer code."""
from google.protobuf import descriptor as _descriptor
from google.protobuf import descriptor_pool as _descriptor_pool
from google.protobuf import symbol_database as _symbol_database
from google.protobuf.internal import builder as _builder
# @@protoc_insertion_point(imports)

_sym_db = _symbol_database.Default()




DESCRIPTOR = _descriptor_pool.Default().AddSerializedFile(b'\n\x0btulip.proto\x12\rmint.tulip.v1\"\x93\x01\n\rSearchRequest\x12\x10\n\x08keywords\x18\x01 \x03(\t\x12\x0c\n\x04\x62ook\x18\x02 \x01(\x05\x12\x34\n\x04page\x18\x63 \x01(\x0b\x32!.mint.tulip.v1.SearchRequest.PageH\x00\x88\x01\x01\x1a#\n\x04Page\x12\r\n\x05index\x18\x01 \x01(\x05\x12\x0c\n\x04size\x18\x02 \x01(\x05\x42\x07\n\x05_page\"\xde\x01\n\x0eSearchResponse\x12\x31\n\x05items\x18\x01 \x03(\x0b\x32\".mint.tulip.v1.SearchResponse.Item\x12/\n\x04page\x18\x62 \x01(\x0b\x32!.mint.tulip.v1.SearchRequest.Page\x12\r\n\x05total\x18\x63 \x01(\x05\x1aY\n\x04Item\x12\x0c\n\x04rank\x18\x01 \x01(\x05\x12\x11\n\thighlight\x18\x02 \x01(\t\x12\x0c\n\x04\x62ook\x18\x03 \x01(\x05\x12\x11\n\tparagraph\x18\x04 \x01(\x05\x12\x0f\n\x07\x63ontent\x18\x05 \x01(\t2O\n\x06Search\x12\x45\n\x04Pali\x12\x1c.mint.tulip.v1.SearchRequest\x1a\x1d.mint.tulip.v1.SearchResponse\"\x00\x42\x32\n.com.github.iapt_platform.mint.plugins.tulip.v1P\x01\x62\x06proto3')

_globals = globals()
_builder.BuildMessageAndEnumDescriptors(DESCRIPTOR, _globals)
_builder.BuildTopDescriptorsAndMessages(DESCRIPTOR, 'tulip_pb2', _globals)
if _descriptor._USE_C_DESCRIPTORS == False:
  DESCRIPTOR._options = None
  DESCRIPTOR._serialized_options = b'\n.com.github.iapt_platform.mint.plugins.tulip.v1P\001'
  _globals['_SEARCHREQUEST']._serialized_start=31
  _globals['_SEARCHREQUEST']._serialized_end=178
  _globals['_SEARCHREQUEST_PAGE']._serialized_start=134
  _globals['_SEARCHREQUEST_PAGE']._serialized_end=169
  _globals['_SEARCHRESPONSE']._serialized_start=181
  _globals['_SEARCHRESPONSE']._serialized_end=403
  _globals['_SEARCHRESPONSE_ITEM']._serialized_start=314
  _globals['_SEARCHRESPONSE_ITEM']._serialized_end=403
  _globals['_SEARCH']._serialized_start=405
  _globals['_SEARCH']._serialized_end=484
# @@protoc_insertion_point(module_scope)
