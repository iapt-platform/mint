/*
 Navicat Premium Data Transfer

 Source Server         : Sentence
 Source Server Type    : SQLite
 Source Server Version : 3035005 (3.35.5)
 Source Schema         : main

 Target Server Type    : SQLite
 Target Server Version : 3035005 (3.35.5)
 File Encoding         : 65001

 Date: 17/08/2023 11:52:09
*/

PRAGMA foreign_keys = false;

-- ----------------------------
-- Table structure for channel
-- ----------------------------
DROP TABLE IF EXISTS "channel";
CREATE TABLE "channel" (
  "id" VARCHAR,
  "name" VARCHAR,
  "type" VARCHAR,
  "language" VARCHAR,
  "summary" VARCHAR,
  "owner_id" VARCHAR,
  "setting" VARCHAR,
  "created_at" DATE,
  PRIMARY KEY ("id")
);

-- ----------------------------
-- Table structure for chapter
-- ----------------------------
DROP TABLE IF EXISTS "chapter";
CREATE TABLE "chapter" (
  "id" VARCHAR,
  "book" INTEGER,
  "paragraph" INTEGER,
  "language" VARCHAR,
  "title" TEXT,
  "channel_id" VARCHAR,
  "progress" DOUBLE,
  "updated_at" DATE
);

-- ----------------------------
-- Table structure for pali_text
-- ----------------------------
DROP TABLE IF EXISTS "pali_text";
CREATE TABLE "pali_text" (
  "id" VARCHAR,
  "book" INTEGER,
  "paragraph" INTEGER,
  "level" INTEGER,
  "toc" VARCHAR,
  "chapter_len" INTEGER,
  "parent" INTEGER,
  PRIMARY KEY ("id")
);

-- ----------------------------
-- Table structure for sentence
-- ----------------------------
DROP TABLE IF EXISTS "sentence";
CREATE TABLE "sentence" (
  "book" INTEGER,
  "paragraph" INTEGER,
  "word_start" INTEGER,
  "word_end" INTEGER,
  "content" VARCHAR,
  "channel_id" VARCHAR
);

-- ----------------------------
-- Table structure for sentence_translation
-- ----------------------------
DROP TABLE IF EXISTS "sentence_translation";
CREATE TABLE "sentence_translation" (
  "book" INTEGER,
  "paragraph" INTEGER,
  "word_start" INTEGER,
  "word_end" INTEGER,
  "content" VARCHAR,
  "channel_id" VARCHAR
);

-- ----------------------------
-- Table structure for tag
-- ----------------------------
DROP TABLE IF EXISTS "tag";
CREATE TABLE "tag" (
  "id" VARCHAR,
  "name" VARCHAR,
  "description" TIME,
  "color" INTEGER,
  "owner_id" VARCHAR,
  PRIMARY KEY ("id")
);

-- ----------------------------
-- Table structure for tag_map
-- ----------------------------
DROP TABLE IF EXISTS "tag_map";
CREATE TABLE "tag_map" (
  "anchor_id" VARCHAR,
  "tag_id" VARCHAR
);

DROP TABLE IF EXISTS "dhamma_terms";
CREATE TABLE dhamma_terms (
	"uuid" varchar(36) NOT NULL,
	"word" varchar(1024) NOT NULL,
	"word_en" varchar(1024) NOT NULL,
	"meaning" varchar(1024) NOT NULL,
	"other_meaning" varchar(1024) NULL,
	"note" text NULL,
	"tag" varchar(1024) NULL,
	"channel_id" varchar(36) NULL,
	"language" varchar(16) NOT NULL DEFAULT 'zh-hans',
	"owner" varchar(36) NOT NULL,
	"editor_id" INTEGER NOT NULL,
	"created_at" timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP,
	"updated_at" timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP,
	"deleted_at" timestamp(0) NULL,
	CONSTRAINT dhamma_terms_pkey PRIMARY KEY (uuid)
);
CREATE INDEX "dhamma_terms_channel_index" ON "dhamma_terms"  ("channel_id" ASC);
CREATE INDEX "dhamma_terms_created_at_index" ON "dhamma_terms"  ("created_at" ASC);
CREATE INDEX "dhamma_terms_editor_id_index" ON "dhamma_terms"  ("editor_id" ASC);
CREATE INDEX "dhamma_terms_meaning_index" ON "dhamma_terms"  ("meaning" ASC);
CREATE INDEX "dhamma_terms_owner_index" ON "dhamma_terms"  ("owner" ASC);
CREATE INDEX "dhamma_terms_updated_at_index" ON "dhamma_terms"  ("updated_at" ASC);
CREATE INDEX "dhamma_terms_word_en_index" ON "dhamma_terms"  ("word_en" ASC);
CREATE INDEX "dhamma_terms_word_index" ON "dhamma_terms"  ("word" ASC);

-- ----------------------------
-- Indexes structure for table channel
-- ----------------------------
CREATE INDEX "channel_id"
ON "channel" (
  "id" ASC
);

-- ----------------------------
-- Indexes structure for table chapter
-- ----------------------------
CREATE INDEX "book"
ON "chapter" (
  "book" DESC
);

-- ----------------------------
-- Indexes structure for table pali_text
-- ----------------------------
CREATE UNIQUE INDEX "bp"
ON "pali_text" (
  "book" ASC,
  "paragraph" ASC
);
CREATE UNIQUE INDEX "id"
ON "pali_text" (
  "id" DESC
);

-- ----------------------------
-- Indexes structure for table sentence
-- ----------------------------
CREATE INDEX "bps"
ON "sentence" (
  "book" ASC,
  "paragraph" ASC
);

-- ----------------------------
-- Indexes structure for table sentence_translation
-- ----------------------------
CREATE INDEX "bpst"
ON "sentence_translation" (
  "book" ASC,
  "paragraph" ASC
);

-- ----------------------------
-- Indexes structure for table tag
-- ----------------------------
CREATE UNIQUE INDEX "name"
ON "tag" (
  "name",
  "owner_id"
);

-- ----------------------------
-- Indexes structure for table tag_map
-- ----------------------------
CREATE INDEX "tag_id"
ON "tag_map" (
  "tag_id" DESC
);

PRAGMA foreign_keys = true;
