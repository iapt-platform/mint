CREATE TABLE dhammaterm (
    id            INTEGER   PRIMARY KEY AUTOINCREMENT,
    guid          TEXT (36),
    word          TEXT,
    word_en       TEXT,
    meaning       TEXT,
    other_meaning TEXT,
    note          TEXT,
    tag           TEXT,
    create_time   INTEGER,
    owner         TEXT,
    hit           INTEGER   DEFAULT (0),
    language      CHAR (8),
    receive_time  INTEGER,
    modify_time   INTEGER,
    channal       TEXT
);
