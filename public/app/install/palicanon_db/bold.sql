--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 8月 3 08:36:14 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：bold
DROP TABLE IF EXISTS bold;

CREATE TABLE bold (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    book      INTEGER NOT NULL,
    paragraph INTEGER NOT NULL,
    word      TEXT    NOT NULL,
    word2     TEXT    NOT NULL,
    word_en   TEXT,
    pali      TEXT,
    base      TEXT
);


COMMIT TRANSACTION;
VACUUM;
PRAGMA foreign_keys = on;
