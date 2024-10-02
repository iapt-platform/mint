-- wbw_block definition

CREATE TABLE wbw_block 
(
    id CHAR (36) PRIMARY KEY, 
    parent_id CHAR (36), 
    channal CHAR (36), 
    parent_channel VARCHAR (36), 
    owner CHAR (36), 
    book INTEGER, 
    paragraph INTEGER, 
    style CHAR (16), 
    lang CHAR (8), 
    status INTEGER, 
    modify_time INTEGER, 
    receive_time INTEGER, 
    create_time INTEGER
);