CREATE TYPE TChannelType AS ENUM 
(
	'original',
	'translation',
	'nissaya',
	'commentary'
);
CREATE TABLE channels
(
	id BIGINT PRIMARY KEY, 
	uid VARCHAR (36) NOT NULL, 
	type TChannelType  NOT NULL DEFAULT('translation'),
	owner_uid  VARCHAR (36) NOT NULL, 
	editor_id BIGINT NOT NULL,
	name VARCHAR (64), 
	summary VARCHAR (1024),
	status INTEGER  NOT NULL DEFAULT(10), 
	lang VARCHAR (16), 
	setting TEXT,
	create_time BIGINT NOT NULL, 
	modify_time BIGINT NOT NULL, 
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX channels_owner_uid ON channels (owner_uid);
CREATE INDEX channels_editor_id ON channels (editor_id);
CREATE UNIQUE INDEX channels_uid ON channels (uid);
