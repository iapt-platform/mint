<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: tulip.proto

namespace GPBMetadata;

class Tulip
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(
            '
�

tulip.protomint.tulip.v1"�
SearchRequest
keywords (	
books (

match_mode (	4
pagec (2!.mint.tulip.v1.SearchRequest.PageH �#
Page
index (
size (B
_page"�
SearchResponse1
items (2".mint.tulip.v1.SearchResponse.Item/
pageb (2!.mint.tulip.v1.SearchRequest.Page
totalc (Y
Item
rank (
	highlight (	
book (
	paragraph (
content (	"l
BookListResponse3
items (2$.mint.tulip.v1.BookListResponse.Item#
Item
book (
count ("�
UpdateRequest
book (
	paragraph (
level (
bold1 (	
bold2 (	
bold3 (	
content (	
pcd_book_id ("
UpdateResponse
count ("H
UpdateIndexRequest
book (
	paragraph (H �B

_paragraph"$
UpdateIndexResponse
error ("\'
UploadDictionaryRequest
data (	")
UploadDictionaryResponse
error (2�
SearchE
Pali.mint.tulip.v1.SearchRequest.mint.tulip.v1.SearchResponse" K
BookList.mint.tulip.v1.SearchRequest.mint.tulip.v1.BookListResponse" G
Update.mint.tulip.v1.UpdateRequest.mint.tulip.v1.UpdateResponse" V
UpdateIndex!.mint.tulip.v1.UpdateIndexRequest".mint.tulip.v1.UpdateIndexResponse" e
UploadDictionary&.mint.tulip.v1.UploadDictionaryRequest\'.mint.tulip.v1.UploadDictionaryResponse" B2
.com.github.iapt_platform.mint.plugins.tulip.v1Pbproto3'
        , true);

        static::$is_initialized = true;
    }
}

