-- Your SQL goes here
CREATE TYPE TResourceType AS ENUM ('channel','article','collection','sentence','wbw');
CREATE TYPE TUserType AS ENUM ('user','group');
CREATE TYPE TRightType AS ENUM ('read','write','admin','owner');

CREATE TABLE authorizations (
    id              SERIAL PRIMARY KEY,
    resource_id     INTEGER  NOT NULL,
    resource_type   TResourceType NOT NULL,

    user_id   INTEGER  NOT NULL,
    user_type TUserType NOT NULL , 
    
    res_right       TRightType  NOT NULL,
    owner_id        INTEGER  NOT NULL,
    expired_at      TIMESTAMP,
    accepted_at     TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX authorizations_unique ON authorizations (resource_id,resource_type,user_id,user_type);



