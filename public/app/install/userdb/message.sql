--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 7月 31 13:50:23 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：message


CREATE TABLE message (
    id        INTEGER   PRIMARY KEY AUTOINCREMENT,
    sender    TEXT      NOT NULL,
    type      INTEGER   NOT NULL,
    book      INTEGER,
    paragraph INTEGER,
    data      TEXT,
    doc_id    TEXT (40),
    time      INTEGER
);


COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
