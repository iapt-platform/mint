CREATE TABLE user_dicts 
(
	id SERIAL PRIMARY KEY,
	word VARCHAR(1024)  NOT NULL , 
	type VARCHAR(128), 
	gramma VARCHAR(128), 
	stem VARCHAR(1024), 
	mean TEXT, 
	note TEXT, 
	factors VARCHAR(1024), 
	factormean TEXT, 
	status INTEGER  NOT NULL DEFAULT (1),
	source VARCHAR(256), 
	language VARCHAR(16)  NOT NULL DEFAULT ('en'), 
	confidence INTEGER  NOT NULL DEFAULT (100), 
	creator_id INTEGER   NOT NULL , 
	ref_counter INTEGER DEFAULT (1),
	create_time INTEGER  NOT NULL ,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 索引：
CREATE INDEX user_dicts_word ON user_dicts (word);
CREATE INDEX user_dicts_updated_at_idx ON user_dicts USING btree (updated_at)
CREATE INDEX user_dicts_word ON user_dicts USING btree (word)