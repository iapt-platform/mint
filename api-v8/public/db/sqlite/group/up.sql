--
-- 由SQLiteStudio v3.1.1 产生的文件 周六 2月 5 08:30:59 2022
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：group_info
CREATE TABLE group_info 
(
    id CHAR (36) PRIMARY KEY, 
    parent CHAR (36), 
    name CHAR (32) NOT NULL, 
    description TEXT, 
    create_time INTEGER NOT NULL, 
    status INTEGER, 
    owner CHAR (36), 
    'modify_time' INTEGER
);

-- 表：group_member
CREATE TABLE group_member 
(
    id INTEGER PRIMARY KEY AUTOINCREMENT, 
    user_id CHAR (36) NOT NULL, 
    group_id INTEGER NOT NULL, 
    power INTEGER NOT NULL DEFAULT (1), 
    group_name CHAR (32), 
    level INTEGER DEFAULT (0), 
    status INTEGER DEFAULT (1)
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
