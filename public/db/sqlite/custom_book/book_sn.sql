--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 2月 14 16:11:21 2022
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：setting
CREATE TABLE setting (
    [key]     TEXT PRIMARY KEY,
    value     TEXT,
    [default] TEXT
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
