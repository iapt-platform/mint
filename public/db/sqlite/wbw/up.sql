-- wbw definition

CREATE TABLE wbw (id CHAR (36) PRIMARY KEY, block_id CHAR (36), book INTEGER, paragraph INTEGER, wid INTEGER, word TEXT, data TEXT, modify_time INTEGER, receive_time INTEGER, status INTEGER, owner CHAR (36), create_time INTEGER);

CREATE INDEX wbw_index ON wbw (block_id, wid, modify_time, book, paragraph);