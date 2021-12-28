--
-- 由SQLiteStudio v3.1.1 产生的文件 周五 2月 19 16:39:06 2021
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：group_info
CREATE TABLE group_info (id CHAR (36) PRIMARY KEY, parent CHAR (36), name CHAR (32) NOT NULL, description TEXT, create_time INTEGER NOT NULL, status INTEGER, owner CHAR (36));

-- 表：group_member
CREATE TABLE group_member (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id CHAR (36) NOT NULL, group_id INTEGER NOT NULL, power INTEGER NOT NULL DEFAULT (1), group_name CHAR (32), level INTEGER DEFAULT (0), status INTEGER DEFAULT (1));

-- 表：group_roles
CREATE TABLE group_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, group_id INTEGER NOT NULL, name CHAR (32) NOT NULL, power TEXT NOT NULL);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
