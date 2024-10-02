-- Your SQL goes here

CREATE TABLE ref_dict_names (
    id           SERIAL PRIMARY KEY,

    name        VARCHAR (512) NOT NULL,
    short_name        VARCHAR (32) NOT NULL DEFAULT(''),

    lang      VARCHAR (16) NOT NULL DEFAULT('en'),

    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
