PRAGMA foreign_keys = 0;

CREATE TABLE sqlitestudio_temp_table AS SELECT *
                                          FROM fileindex;

DROP TABLE fileindex;

CREATE TABLE fileindex (
    id          INTEGER   PRIMARY KEY AUTOINCREMENT,
    userid      INTEGER,
    parent_id   TEXT (40),
    doc_id      TEXT (40),
    book        INTEGER   DEFAULT (0),
    paragraph   INTEGER   DEFAULT (0),
    file_name   TEXT      NOT NULL,
    title       TEXT,
    tag         TEXT,
    status      INTEGER   DEFAULT (1),
    create_time INTEGER,
    modify_time INTEGER,
    accese_time INTEGER,
    file_size   INTEGER,
    share       INTEGER   DEFAULT (0),
    doc_info    TEXT,
    doc_block   TEXT
);

INSERT INTO fileindex (
                          id,
                          userid,
                          parent_id,
                          doc_id,
                          book,
                          paragraph,
                          file_name,
                          title,
                          tag,
                          status,
                          create_time,
                          modify_time,
                          accese_time,
                          file_size,
                          share
                      )
                      SELECT id,
                             userid,
                             parent_id,
                             doc_id,
                             book,
                             paragraph,
                             file_name,
                             title,
                             tag,
                             status,
                             create_time,
                             modify_time,
                             accese_time,
                             file_size,
                             share
                        FROM sqlitestudio_temp_table;

DROP TABLE sqlitestudio_temp_table;

PRAGMA foreign_keys = 1;