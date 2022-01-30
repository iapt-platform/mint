CREATE TYPE TOpType AS ENUM 
(
	'channel_update',
	'channel_create',
	'article_update',
	'article_create',
	'dict_lookup',
	'term_create',
	'term_update',
	'term_lookup',
	'wbw_update',
	'wbw_create',
	'sent_update',
	'sent_create',
	'collection_update',
	'collection_create',
	'nissaya_open'
);
CREATE TABLE user_operation_logs 
(
	id SERIAL PRIMARY KEY, 
	user_id INTEGER  NOT NULL, 
	op_type_id INTEGER NOT NULL , 
	op_type TOpType NOT NULL , 
	content TEXT, 
	timezone BIGINT  NOT NULL,
	create_time  BIGINT  NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX user_operation_logs_user_id ON user_operation_logs (user_id);
